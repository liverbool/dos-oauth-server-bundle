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
use Dos\OAuthServerBundle\Model\ScopeInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    /**
     * @param string $identifier
     *
     * @return ScopeInterface|null|object
     */
    private function findEnabledScope(string $identifier): ?ScopeInterface
    {
        return $this->findOneBy(['identifier' => $identifier, 'enabled' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeInterface
    {
        return $this->findEnabledScope($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        return $scopes;
    }

    /**
     * @return QueryBuilder
     */
    public function createListQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->join('o.translations', 'translation');

        return $queryBuilder;
    }
}
