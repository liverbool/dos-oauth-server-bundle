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

use Dos\OAuthServerBundle\Model\ScopeInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

interface RestrictionCheckerInterface
{
    /**
     * @param string $clientId
     * @param string $grantTypeId
     *
     * @return bool
     */
    public function isClientSupportsGrant(string $clientId, string $grantTypeId): bool;

    /**
     * @param string $clientId
     * @param string $grantTypeId
     * @param ScopeInterface[]|ScopeEntityInterface[] $scopes
     *
     * @return bool
     */
    public function isClientSupportsScope(string $clientId, string $grantTypeId, array $scopes): bool;

    /**
     * @return ScopeInterface[]|ScopeEntityInterface[]
     */
    public function getFailedScopes(): array;

    /**
     * @return array
     */
    public function getFailedScopeIds(): array;

    /**
     * @return string
     */
    public function getFailedScopesInString(): string;
}
