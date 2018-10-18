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
use League\OAuth2\Server\Exception\OAuthServerException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    /**
     * @param string $clientId
     *
     * @return ClientInterface|null|object
     */
    public function findEnabledClient(string $clientId): ?ClientInterface
    {
        return $this->findOneBy(['identifier' => $clientId, 'enabled' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true): ?ClientInterface
    {
        if (!$client = $this->findEnabledClient($clientIdentifier)) {
            return null;
        }

        if (!in_array($grantType, $client->getGrantTypes())) {
            // ... or just return?
            throw OAuthServerException::unsupportedGrantType();
            //return;
        }

        if ($mustValidateSecret && !hash_equals($client->getSecret(), (string)$clientSecret)) {
            return null;
        }

        return $client;
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
