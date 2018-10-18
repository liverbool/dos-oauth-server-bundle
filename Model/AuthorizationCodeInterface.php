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

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\ToggleableInterface;

interface AuthorizationCodeInterface extends
    AuthCodeEntityInterface,
    ResourceInterface,
    ToggleableInterface,
    TimestampableInterface,
    UserAwareInterface,
    NativeUserAwareInterface
{
}
