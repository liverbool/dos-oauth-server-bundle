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
use Dos\OAuthServerBundle\Model\AuthorizationCodeInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class AuthorizationCodeRepository extends EntityRepository implements AuthCodeRepositoryInterface
{
    /**
     * @param string $code
     *
     * @return AuthorizationCodeInterface|null|object
     */
    private function findAuthorizationCode(string $code): ?AuthorizationCodeInterface
    {
        return $this->findOneBy(['identifier' => $code]);
    }

    /**
     * @return AuthorizationCodeInterface
     */
    public function getNewAuthCode(): AuthorizationCodeInterface
    {
        return new $this->_entityName();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        try {
            $this->_em->persist($authCodeEntity);
            $this->_em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId): void
    {
        if (!$object = $this->findAuthorizationCode($codeId)) {
            return;
        }

        $object->setEnabled(false);

        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        if (!$object = $this->findAuthorizationCode($codeId)) {
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
