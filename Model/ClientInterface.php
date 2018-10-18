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

namespace Dos\OAuthServerBundle\Model;

use Doctrine\Common\Collections\Collection;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use PhpMob\MediaBundle\Model\FileAwareInterface;
use Sylius\Component\Resource\Model\ToggleableInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;

interface ClientInterface extends
    ClientTranslationInterface,
    ClientEntityInterface,
    FileAwareInterface,
    ToggleableInterface,
    TranslatableInterface,
    UserAwareInterface
{
    /**
     * @return bool
     */
    public function isAuthorizedRequire(): bool;

    /**
     * @param bool $authorizedRequire
     */
    public function setAuthorizedRequire(bool $authorizedRequire): void;

    /**
     * @param $identifier
     */
    public function setIdentifier($identifier);

    /**
     * @return string
     */
    public function getSecret(): string;

    /**
     * @param string $clientSecret
     */
    public function setSecret(string $clientSecret): void;

    /**
     * @return array
     */
    public function getGrantTypes(): array;

    /**
     * @param array $grantTypes
     */
    public function setGrantTypes(array $grantTypes): void;

    /**
     * @param array $redirectUris
     */
    public function setRedirectUris(array $redirectUris): void;

    /**
     * @return array
     */
    public function getRedirectUris(): array;

    /**
     * @return ClientLogoInterface
     */
    public function getLogo(): ?ClientLogoInterface;

    /**
     * @param ClientLogoInterface $logo
     */
    public function setLogo(?ClientLogoInterface $logo): void;

    /**
     * @return Collection|UserInterface[]
     */
    public function getAuthorizedUsers(): Collection;

    /**
     * @param Collection $authorizedUsers
     */
    public function setAuthorizedUsers(Collection $authorizedUsers): void;

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function hasAuthorizedUser(UserInterface $user): bool;

    /**
     * @param UserInterface $user
     */
    public function addAuthorizedUser(UserInterface $user): void;

    /**
     * @param UserInterface $user
     */
    public function removeAuthorizedUser(UserInterface $user): void;

    /**
     * @return int
     */
    public function getTotalAuthorizedUsers(): int;

    /**
     * @return int
     */
    public function getTotalAccessTokens(): int;

    /**
     * @return int
     */
    public function getTotalAuthorizationCodes(): int;

    /**
     * @param array $supportsScopes
     */
    public function setSupportsScopes(array $supportsScopes): void;

    /**
     * @return ScopeInterface[]
     */
    public function getSupportsScopes(): array;

    /**
     * @return string[]
     */
    public function getSupportsScopeIds(): array;

    /**
     * @param ScopeInterface $scope
     */
    public function addScope(ScopeInterface $scope): void;

    /**
     * @param ScopeInterface $scope
     */
    public function removeScope(ScopeInterface $scope): void;

    /**
     * @param ScopeInterface $scope
     *
     * @return bool
     */
    public function hasScope(ScopeInterface $scope): bool;
}
