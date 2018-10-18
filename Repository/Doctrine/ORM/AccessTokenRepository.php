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

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\QueryBuilder;
use Dos\OAuthServerBundle\Model\AccessTokenInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class AccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{
    /**
     * @param string $token
     *
     * @return AccessTokenInterface|null|object
     */
    private function findTokenById(string $token): ?AccessTokenInterface
    {
        return $this->findOneBy(['identifier' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenInterface
    {
        return new $this->_entityName();
    }

    /**
     * {@inheritdoc}
     *
     * @param AccessTokenInterface $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        try {
            $this->_em->persist($accessTokenEntity);
            $this->_em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId): void
    {
        if (!$object = $this->findTokenById($tokenId)) {
            return;
        }

        $object->setEnabled(false);

        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        if (!$object = $this->findTokenById($tokenId)) {
            return true;
        }

        return !$object->isEnabled();
    }

    /**
     * @return QueryBuilder
     */
    public function createListQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->join('o.user', 'user')
            ->join('o.client', 'client')
            ->join('client.translations', 'clientTranslation')
        ;

        return $queryBuilder;
    }
}
