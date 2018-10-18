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

use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ToggleableTrait;

class AuthorizedUser implements AuthorizedUserInterface
{
    use TimestampableTrait, ToggleableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var string
     */
    protected $grantType;

    /**
     * @var string[]
     */
    protected $scopes = [];

    public function __construct()
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function addScope(string $scope): void
    {
        if (!in_array($scope, $this->scopes)) {
            $this->scopes[] = $scope;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantType(): string
    {
        return $this->grantType;
    }

    /**
     * {@inheritdoc}
     */
    public function setGrantType(string $grantType): void
    {
        $this->grantType = $grantType;
    }
}
