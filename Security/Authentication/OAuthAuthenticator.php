<?php

/*
 * This file is part of the Doss package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dos\OAuthServerBundle\Security\Authentication;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

final class OAuthAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var ResourceServer
     */
    protected $resourceServer;

    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    public function __construct(
        UserCheckerInterface $userChecker,
        UserProviderInterface $userProvider,
        ResourceServer $resourceServer
    )
    {
        $this->userProvider = $userProvider;
        $this->resourceServer = $resourceServer;
        $this->userChecker = $userChecker;
    }

    /**
     * {@inheritdoc}
     *
     * @param TokenInterface|OAuthToken $token
     * @param UserProviderInterface|UserProvider $userProvider
     * @param string $providerKey
     *
     * @return OAuthToken
     *
     * @throws OAuthServerException
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $psr7Factory = new DiactorosFactory();
        $psr7Request = $psr7Factory->createRequest($token->getAttribute('request'));

        try {
            $request = $this->resourceServer->validateAuthenticatedRequest($psr7Request);
        } catch (OAuthServerException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new OAuthServerException($e->getMessage(), 0, 'unknown_error', 500);
        }

        if (!$user = $userProvider->loadUserByUsername($request->getAttribute('oauth_user_id'))) {
            throw OAuthServerException::invalidCredentials();
        }

        try {
            $this->userChecker->checkPreAuth($user);
        } catch (AccountStatusException $e) {
            throw OAuthServerException::invalidCredentials();
        }

        $roles = $user->getRoles();

        foreach ((array) $request->getAttribute('oauth_scopes') as $role) {
            $roles[] = 'ROLE_' . strtoupper($role);
        }

        $token = new OAuthToken(array_unique($roles, SORT_REGULAR));
        $token->setAuthenticated(true);
        $token->setUser($user);
        $token->setAttributes($request->getAttributes());

        try {
            $this->userChecker->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            throw OAuthServerException::invalidCredentials();
        }

        return $token;
    }

    /**
     * @param Request $request
     * @param string $providerKey
     *
     * @return OAuthToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $token = new OAuthToken();
        $token->setAttribute('request', clone $request);
        $token->setAttribute('providerKey', $providerKey);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof OAuthToken && $token->getAttribute('providerKey') === $providerKey;
    }
}
