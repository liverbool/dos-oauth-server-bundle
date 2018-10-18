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

namespace Dos\OAuthServerBundle\Context;

use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\UserInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

interface ClientContextInterface
{
    /**
     * @return ClientInterface
     *
     * @throws NotFoundHttpException
     */
    public function getClient(): ClientInterface;

    /**
     * @return UserInterface
     *
     * @throws NotFoundHttpException
     */
    public function getUser(): UserInterface;

    /**
     * Get the client's identifier.
     *
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function getIdentifier(): string;

    /**
     * Get the client's name.
     *
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function getName(): string;

    /**
     * Get the client's description.
     *
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function getDescription(): string;
}
