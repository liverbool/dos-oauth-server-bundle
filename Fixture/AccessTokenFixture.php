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

namespace Dos\OAuthServerBundle\Fixture;

use PhpMob\DataFixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class AccessTokenFixture extends AbstractResourceFixture
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'dos_access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode)
    {
        $resourceNode
            ->children()
            ->scalarNode('client')->cannotBeEmpty()->end()
            ->scalarNode('identifier')->cannotBeEmpty()->end()
            ->scalarNode('user')->cannotBeEmpty()->end()
            ->scalarNode('expires_in')->end()
            ->scalarNode('scope')->end()
        ;
    }
}
