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

use Dos\OAuthServerBundle\Mixin\ResourceIdTrait;
use Dos\OAuthServerBundle\Mixin\TokenEntityTrait;
use Dos\OAuthServerBundle\Mixin\UserAwareTrait;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ToggleableTrait;

class AccessToken implements AccessTokenInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;
    use ResourceIdTrait, ToggleableTrait, TimestampableTrait, UserAwareTrait;

    /**
     * @return int
     */
    public function getUserIdentifier()
    {
        return intval($this->user ? $this->user->getId() : $this->userIdentifier);
    }

    /**
     * {@inheritdoc)
     */
    public function getRawUserId(): string
    {
        return (string)$this->userIdentifier;
    }
}
