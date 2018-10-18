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
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

/**
 * @method ScopeTranslation getTranslation(?string $locale = null)
 */
class Scope implements ScopeInterface
{
    use EntityTrait;
    use ResourceIdTrait, ToggleableTrait, TimestampableTrait;
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    public function __construct(?string $identifier = null)
    {
        $this->identifier = $identifier;
        $this->initializeTranslationsCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setName(?string $name): void
    {
        $this->getTranslation()->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return (string)$this->getTranslation()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(?string $description): void
    {
        $this->getTranslation()->setDescription($description);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return (string)$this->getTranslation()->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslation(): TranslationInterface
    {
        return new ScopeTranslation();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return strval($this->identifier);
    }
}
