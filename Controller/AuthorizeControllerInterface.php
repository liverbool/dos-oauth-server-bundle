<?php

/*
 * This file is part of the PhpMob package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dos\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface AuthorizeControllerInterface
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function requestAuthorizeAction(Request $request): Response;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function approveAuthorizeAction(Request $request): Response;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function denyAuthorizeAction(Request $request): Response;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function accessTokenAction(Request $request): Response;
}
