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
use Dos\OAuthServerBundle\Model\RefreshTokenInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class RefreshTokenRepository extends EntityRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @param string $token
     *
     * @return RefreshTokenInterface|null|object
     */
    private function findTokenById(string $token): ?RefreshTokenInterface
    {
        return $this->findOneBy(['identifier' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken(): RefreshTokenInterface
    {
        return new $this->_entityName();
    }

    /**
     * {@inheritdoc}
     *
     * @param RefreshTokenInterface $accessTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        try {
            $this->_em->persist($refreshTokenEntity);
            $this->_em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId): void
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
    public function isRefreshTokenRevoked($tokenId): bool
    {
        if (!$object = $this->findTokenById($tokenId)) {
            return true;
        }

        if ($object->isEnabled()) {
            return !$object->getAccessToken()->isEnabled();
        }

        return true;
    }

    /**
     * @return QueryBuilder
     */
    public function createListQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->addSelect('accessToken')
            ->addSelect('user')
            ->addSelect('client')
            ->addSelect('clientTranslation')
            ->join('o.accessToken', 'accessToken')
            ->join('accessToken.user', 'user')
            ->join('accessToken.client', 'client')
            ->join('client.translations', 'clientTranslation')
        ;

        return $queryBuilder;
    }
}
