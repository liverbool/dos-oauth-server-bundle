<?php

/*
 * This file is part of the PhpMob package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dos\OAuthServerBundle\Controller;

use Dos\OAuthServerBundle\Checker\RestrictionCheckerInterface;
use Dos\OAuthServerBundle\Form\Type\AuthorizeFormType;
use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\UserInterface;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\AuthorizedUserRepositoryInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zend\Diactoros\Response as Psr7Response;

class AuthorizeController implements AuthorizeControllerInterface
{
    /**
     * @var RestrictionCheckerInterface
     */
    private $restrictionChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizedUserRepositoryInterface
     */
    private $authorizedUserRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    public function __construct(
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        AuthorizedUserRepositoryInterface $authorizedUserRepository,
        RestrictionCheckerInterface $restrictionChecker,
        AuthorizationServer $authorizationServer
    )
    {
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->authorizedUserRepository = $authorizedUserRepository;
        $this->tokenStorage = $tokenStorage;
        $this->restrictionChecker = $restrictionChecker;
        $this->authorizationServer = $authorizationServer;
    }

    /**
     * {@inheritdoc}
     */
    public function requestAuthorizeAction(Request $request): Response
    {
        if (!$request->isMethod('GET')) {
            throw new MethodNotAllowedHttpException(['GET']);
        }

        return $this->handleAuthorize($request);
    }

    /**
     * {@inheritdoc}
     */
    public function approveAuthorizeAction(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        return $this->handleAuthorize($request);
    }

    /**
     * {@inheritdoc}
     */
    public function denyAuthorizeAction(Request $request): Response
    {
        if (!$request->isMethod('DELETE')) {
            throw new MethodNotAllowedHttpException(['DELETE']);
        }

        return $this->handleAuthorize($request);
    }

    /**
     * {@inheritdoc}
     */
    public function accessTokenAction(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        try {
            $response = $this->authorizationServer->respondToAccessTokenRequest(
                $this->convertRequest($request), new Psr7Response()
            );
        } catch (OAuthServerException $exception) {
            // forward to \Dos\OAuthServerBundle\EventListener\ExceptionSubscriber
            throw $exception;
        }

        return self::reverseResponse($response);
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        return $token->getUser();
    }

    /**
     * @param Request $request
     * @param AuthorizationRequest $authRequest
     *
     * @return null|Response
     */
    private function handleAuthorizeForm(Request $request, AuthorizationRequest $authRequest): ?Response
    {
        /** @var ClientInterface $client */
        $client = $authRequest->getClient();

        if (!$client->isAuthorizedRequire()) {
            $authRequest->setAuthorizationApproved(true);

            return null;
        }

        $approved = false;
        $form = $this->formFactory->create(
            $request->attributes->get('_form', AuthorizeFormType::class)
        );

        if ($request->isMethod('GET') && $this->isUserAlreadyApproved($authRequest)) {
            $approved = true;
        }

        if ($request->isMethod('GET') && false === $approved) {
            return $this->renderFormView($request, $authRequest, $form);
        }

        if ($request->isMethod('POST')) {
            if (!$form->handleRequest($request)->isValid()) {
                return $this->renderFormView($request, $authRequest, $form);
            }

            $approved = true;
        }

        if ($request->isMethod('DELETE')) {
            if (!$form->handleRequest($request)->isValid()) {
                return $this->renderFormView($request, $authRequest, $form);
            }

            $approved = false;
        }

        // Once the user has approved or denied the client update the status
        // (true = approved, false = denied)
        $authRequest->setAuthorizationApproved($approved);

        // update latest approve status
        $this->authorizedUserRepository->updateLatestApprove(
            $authRequest->getUser(),
            $authRequest->getClient(),
            $authRequest->getGrantTypeId(),
            $authRequest->getScopes(),
            $authRequest->isAuthorizationApproved()
        );

        return null;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws OAuthServerException
     */
    private function handleAuthorize(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Need to login.');
        }

        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            // The auth request object can be serialized into a user's session
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($this->convertRequest($request));

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($user);

            // check server requirements
            $this->checkClientSupportsGrant($authRequest);
            $this->checkClientSupportsScope($authRequest);

            if ($response = $this->handleAuthorizeForm($request, $authRequest)) {
                return $response;
            }

            // Return the HTTP redirect response
            $response = $this->authorizationServer->completeAuthorizationRequest($authRequest, new Psr7Response());
        } catch (OAuthServerException $exception) {
            // forward to \Dos\OAuthServerBundle\EventListener\ExceptionSubscriber
            throw $exception;
        }

        // Logout user
        // @see https://software-security.sans.org/blog/2011/03/07/oauth-authorization-attacks-secure-implementation
        // @warning This will only work when remember me functionality is disabled, BUT make sense!
        // @see https://stackoverflow.com/questions/6464754/symfony2-how-to-log-user-out-manually-in-controller
        // @note need to use ngrok prevent all of 127.* ip session lose.
        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        return self::reverseResponse($response);
    }

    /**
     * @param Request $request
     * @param AuthorizationRequest $authRequest
     * @param FormInterface $form
     *
     * @return Response
     */
    private function renderFormView(Request $request, AuthorizationRequest $authRequest, FormInterface $form): Response
    {
        $template = $request->attributes->get('_template', '@DosOAuthServer/authorize.html.twig');

        return $this->templating->renderResponse($template, [
            'approve' => $form->createView(),
            'deny' => $form->createView(),
            'authRequest' => $authRequest,
        ]);
    }

    /**
     * @param AuthorizationRequest $authRequest
     *
     * @return bool
     */
    private function isUserAlreadyApproved(AuthorizationRequest $authRequest): bool
    {
        // never approve
        if (!$approvedUser = $this->authorizedUserRepository->findApprovedUser(
            $authRequest->getUser(), $authRequest->getClient()
        )) {
            return false;
        }

        $requestScopes = [];

        foreach ($authRequest->getScopes() as $scope) {
            $requestScopes[] = $scope->getIdentifier();
        }

        // request diff scopes?
        return empty(array_diff($requestScopes, $approvedUser->getScopes()));
    }

    /**
     * @param AuthorizationRequest $request
     *
     * @throws OAuthServerException
     */
    private function checkClientSupportsGrant(AuthorizationRequest $request)
    {
        if (!$this->restrictionChecker->isClientSupportsGrant(
            $request->getClient()->getIdentifier(),
            $request->getGrantTypeId())
        ) {
            throw OAuthServerException::unsupportedGrantType();
        }
    }

    /**
     * @param AuthorizationRequest $request
     *
     * @throws OAuthServerException
     */
    private function checkClientSupportsScope(AuthorizationRequest $request)
    {
        if (!$this->restrictionChecker->isClientSupportsScope(
            $request->getClient()->getIdentifier(),
            $request->getGrantTypeId(),
            $request->getScopes())
        ) {
            throw OAuthServerException::invalidScope($this->restrictionChecker->getFailedScopesInString());
        }
    }


    /**
     * {@inheritdoc}
     */
    private static function convertRequest(Request $request): ServerRequestInterface
    {
        return (new DiactorosFactory())->createRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    private static function reverseResponse(ResponseInterface $response): Response
    {
        return (new HttpFoundationFactory())->createResponse($response);
    }
}
