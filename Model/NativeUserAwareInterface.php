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

namespace Dos\OAuthServerBundle\Model;

interface NativeUserAwareInterface
{
    /**
     * @return string
     */
    public function getRawUserId(): string;

    /**
     * @param UserInterface|null $user
     */
    public function setUser(?UserInterface $user): void;

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;
}
