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

use Sylius\Component\Resource\Model\ResourceInterface;

interface ScopeTranslationInterface extends ResourceInterface
{
    /**
     * @param null|string $name
     */
    public function setName(?string $name): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void;

    /**
     * @return string
     */
    public function getDescription(): string;
}
