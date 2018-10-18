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

use Doctrine\ORM\QueryBuilder;
use Dos\OAuthServerBundle\Model\ClientInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface as LeagueClientRepositoryInterface;

interface ClientRepositoryInterface extends LeagueClientRepositoryInterface
{
    /**
     * @param string $clientId
     *
     * @return ClientInterface|null|object
     */
    public function findEnabledClient(string $clientId): ?ClientInterface;

    /**
     * @return QueryBuilder
     */
    public function createListQueryBuilder(): QueryBuilder;
}
