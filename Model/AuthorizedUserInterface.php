<?php

namespace Dos\OAuthServerBundle\Model;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\ToggleableInterface;

interface AuthorizedUserInterface extends ResourceInterface, TimestampableInterface, ToggleableInterface
{
    /**
     * @return ClientInterface
     */
    public function getClient(): ClientInterface;

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client): void;

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user): void;

    /**
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * @param string[] $scopes
     */
    public function setScopes(array $scopes): void;

    /**
     * @param string $scope
     */
    public function addScope(string $scope): void;

    /**
     * @return string
     */
    public function getGrantType(): string;

    /**
     * @param string $grantType
     */
    public function setGrantType(string $grantType): void;
}
