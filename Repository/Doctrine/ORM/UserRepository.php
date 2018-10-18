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

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Dos\OAuthServerBundle\Model\UserInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function setEncoderFactory(EncoderFactoryInterface $encoderFactory): void
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param UserCheckerInterface $userChecker
     */
    public function setUserChecker(UserCheckerInterface $userChecker): void
    {
        $this->userChecker = $userChecker;
    }

    /**
     * @param $identifier
     * @return mixed|UserInterface|null
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findUserByEmailOrUsernameOrId($identifier): ?UserInterface
    {
        return $this->createQueryBuilder('o')
            ->where('o.username = :username')
            ->orWhere('o.email = :email')
            ->orWhere('o.id = :id')
            ->setParameter('username', $identifier)
            ->setParameter('email', $identifier)
            ->setParameter('id', $identifier)
            ->getQuery()->getSingleResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        try {
            $user = $this->createQueryBuilder('o')
                ->where('o.username = :username')
                ->orWhere('o.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        } catch (NonUniqueResultException $e) {
            return null;
        }

        try {
            $this->userChecker->checkPreAuth($user);
        } catch (AccountStatusException $e) {
            return null;
        }

        if (!$this->isPasswordValid($user, $password)) {
            return null;
        }

        try {
            $this->userChecker->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            return null;
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     * @param string $password
     *
     * @return bool
     */
    private function isPasswordValid(UserInterface $user, $password): bool
    {
        return $this->encoderFactory
            ->getEncoder($user)
            ->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }
}
