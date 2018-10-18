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

namespace Dos\OAuthServerBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Dos\OAuthServerBundle\Model\NativeUserAwareInterface;
use Dos\OAuthServerBundle\Security\Authentication\UserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ResolverNativeUserListener
{
    /**
     * @var UserProviderInterface|UserProvider
     */
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof NativeUserAwareInterface) {
            return;
        }

        if ($object->getUser()) {
            return;
        }

        $object->setUser($this->userProvider->loadUserByUsername($object->getRawUserId()));
    }
}
