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

namespace Dos\OAuthServerBundle\Checker;

use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\ScopeInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class RestrictionChecker implements RestrictionCheckerInterface
{
    /**
     * @var ClientRepositoryInterface
     */
    protected $clientRepository;

    /**
     * @var bool
     */
    private $enableClientGrantRestrict;

    /**
     * @var bool
     */
    private $enableClientScopeRestrict;

    /**
     * @var array
     */
    private $failedScopes = [];

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        bool $enableClientScopeRestrict = true,
        bool $enableClientGrantRestrict = true
    )
    {
        $this->clientRepository = $clientRepository;
        $this->enableClientScopeRestrict = $enableClientScopeRestrict;
        $this->enableClientGrantRestrict = $enableClientGrantRestrict;
    }

    /**
     * @param string $identifier
     * @param string $grantType
     *
     * @return ClientInterface|ClientEntityInterface
     */
    private function getClient(string $identifier, string $grantType): ClientInterface
    {
        return $this->clientRepository->getClientEntity($identifier, $grantType);
    }

    /**
     * @param string $clientId
     * @param string $grantTypeId
     *
     * @return bool
     */
    public function isClientSupportsGrant(string $clientId, string $grantTypeId): bool
    {
        if (false === $this->enableClientGrantRestrict) {
            return true;
        }

        return in_array($grantTypeId, $this->getClient($clientId, $grantTypeId)->getGrantTypes());
    }

    /**
     * @param string $clientId
     * @param string $grantTypeId
     * @param ScopeInterface[] $scopes
     *
     * @return bool
     */
    public function isClientSupportsScope(string $clientId, string $grantTypeId, array $scopes): bool
    {
        if (false === $this->enableClientScopeRestrict) {
            return true;
        }

        $this->failedScopes = [];
        $supportsScopeIds = $this->getClient($clientId, $grantTypeId)->getSupportsScopeIds();

        foreach ($scopes as $scope) {
            if (!in_array($scope->getIdentifier(), $supportsScopeIds)) {
                $this->failedScopes[$scope->getIdentifier()] = $scope;
            }
        }

        return empty($this->failedScopes);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailedScopes(): array
    {
        return $this->failedScopes;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailedScopeIds(): array
    {
        return array_keys($this->failedScopes);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailedScopesInString(): string
    {
        return implode(' ', $this->getFailedScopeIds());
    }
}
