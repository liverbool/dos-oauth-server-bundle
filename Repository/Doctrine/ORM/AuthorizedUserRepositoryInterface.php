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

namespace Dos\OAuthServerBundle\Repository\Doctrine\ORM;

use Dos\OAuthServerBundle\Model\AuthorizedUserInterface;
use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\ScopeInterface;
use Dos\OAuthServerBundle\Model\UserInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;

interface AuthorizedUserRepositoryInterface
{
    /**
     * @param UserInterface|UserEntityInterface $user
     * @param ClientInterface|ClientEntityInterface $client
     *
     * @return object|AuthorizedUserInterface|null
     */
    public function findApprovedUser(UserInterface $user, ClientInterface $client): ?AuthorizedUserInterface;

    /**
     * @param UserInterface|UserEntityInterface $user
     * @param ClientInterface|ClientEntityInterface $client
     * @param string $grantType
     * @param ScopeInterface[]|ScopeEntityInterface[] $scopes
     * @param bool $approved
     *
     * @return AuthorizedUserInterface
     */
    public function updateLatestApprove(
        UserInterface $user,
        ClientInterface $client,
        string $grantType,
        array $scopes,
        bool $approved): AuthorizedUserInterface;
}
