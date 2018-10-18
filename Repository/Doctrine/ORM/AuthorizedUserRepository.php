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
use Dos\OAuthServerBundle\Model\UserInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class AuthorizedUserRepository extends EntityRepository implements AuthorizedUserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findApprovedUser(UserInterface $user, ClientInterface $client): ?AuthorizedUserInterface
    {
        return $this->findOneBy(['user' => $user, 'client' => $client, 'enabled' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLatestApprove(UserInterface $user, ClientInterface $client, string $grantType, array $scopes, bool $approved): AuthorizedUserInterface
    {
        /** @var AuthorizedUserInterface $object */
        if (!$object = $this->findOneBy(['user' => $user, 'client' => $client])) {
            $object = new $this->_entityName();
        }

        $object->setUser($user);
        $object->setClient($client);
        $object->setGrantType($grantType);
        $object->setEnabled($approved);

        foreach ($scopes as $scope) {
            $object->addScope($scope->getIdentifier());
        }

        $this->_em->persist($object);
        $this->_em->flush();

        return $object;
    }
}
