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

namespace Dos\OAuthServerBundle\Context;

use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\UserInterface;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\ClientRepositoryInterface;
use Dos\OAuthServerBundle\Security\Authentication\OAuthToken;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenBasedClientContext implements ClientContextInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ClientRepositoryInterface
     */
    private $repository;

    public function __construct(TokenStorageInterface $tokenStorage, ClientRepositoryInterface $repository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(): ClientInterface
    {
        /** @var OAuthToken $token */
        if (!$token = $this->tokenStorage->getToken()) {
            throw new NotFoundHttpException('Client token not found.');
        }

        if (!$client = $this->repository->findEnabledClient($token->getAttribute('oauth_client_id'))) {
            throw new NotFoundHttpException('Client not found.');
        }

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): UserInterface
    {
        return $this->getClient()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->getClient()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return $this->getClient()->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->getClient()->getDescription();
    }
}
