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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Dos\OAuthServerBundle\Mixin\ResourceIdTrait;
use Dos\OAuthServerBundle\Mixin\UserAwareTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

/**
 * @method ClientTranslation getTranslation(?string $locale = null)
 */
class Client implements ClientInterface
{
    use EntityTrait;
    use ResourceIdTrait, TimestampableTrait, ToggleableTrait, UserAwareTrait;
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    /**
     * @var bool
     */
    protected $authorizedRequire = true;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var array
     */
    protected $grantTypes = [];

    /**
     * @var array
     */
    protected $redirectUris = [];

    /**
     * @var ClientLogoInterface
     */
    protected $logo;

    /**
     * @var Collection
     */
    protected $authorizedUsers;

    /**
     * @var Collection
     */
    protected $accessTokens;

    /**
     * @var Collection
     */
    protected $authorizationCodes;

    /**
     * @var Collection
     */
    protected $supportsScopes;

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->authorizedUsers = new ArrayCollection();
        $this->accessTokens = new ArrayCollection();
        $this->authorizationCodes = new ArrayCollection();
        $this->supportsScopes = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorizedRequire(): bool
    {
        return (bool)$this->authorizedRequire;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizedRequire(bool $authorizedRequire): void
    {
        $this->authorizedRequire = $authorizedRequire;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUri()
    {
        return $this->getRedirectUris();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return (string)$this->getTranslation()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setName(?string $name): void
    {
        $this->getTranslation()->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return (string)$this->getTranslation()->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(?string $description = null): void
    {
        $this->getTranslation()->setDescription($description);
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantTypes(): array
    {
        return $this->grantTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setGrantTypes(array $grantTypes): void
    {
        $this->grantTypes = $grantTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectUris(array $redirectUris): void
    {
        $this->redirectUris = array_unique($redirectUris);
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUris(): array
    {
        return array_values($this->redirectUris);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogo(): ?ClientLogoInterface
    {
        return $this->logo;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogo(?ClientLogoInterface $logo): void
    {
        $this->logo = $logo ? ($logo->getFile() ? $logo : null) : null;

        if ($this->logo) {
            $logo->setOwner($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFileBasePath()
    {
        return '/private/oauth-clients';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizedUsers(): Collection
    {
        return $this->authorizedUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizedUsers(Collection $authorizedUsers): void
    {
        $this->authorizedUsers = $authorizedUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizedUser(UserInterface $user): bool
    {
        return $this->authorizedUsers->contains($user);
    }

    /**
     * {@inheritdoc}
     */
    public function addAuthorizedUser(UserInterface $user): void
    {
        if (!$this->hasAuthorizedUser($user)) {
            $this->authorizedUsers->add($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAuthorizedUser(UserInterface $user): void
    {
        if ($this->hasAuthorizedUser($user)) {
            $this->authorizedUsers->removeElement($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalAuthorizedUsers(): int
    {
        return $this->authorizedUsers->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalAccessTokens(): int
    {
        return $this->accessTokens->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalAuthorizationCodes(): int
    {
        return $this->authorizationCodes->count();
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslation(): TranslationInterface
    {
        return new ClientTranslation();
    }

    /**
     * {@inheritdoc}
     */
    public function setSupportsScopes(array $supportsScopes): void
    {
        $this->supportsScopes->clear();

        foreach ($supportsScopes as $scope) {
            $this->addScope($scope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportsScopes(): array
    {
        return $this->supportsScopes->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportsScopeIds(): array
    {
        return $this->supportsScopes->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function addScope(ScopeInterface $scope): void
    {
        if (!$this->hasScope($scope)) {
            $this->supportsScopes->set($scope->getIdentifier(), $scope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeScope(ScopeInterface $scope): void
    {
        if ($this->hasScope($scope)) {
            $this->supportsScopes->remove($scope->getIdentifier(), $scope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasScope(ScopeInterface $scope): bool
    {
        return $this->supportsScopes->containsKey($scope->getIdentifier());
    }
}
