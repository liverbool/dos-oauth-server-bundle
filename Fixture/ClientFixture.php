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

class ClientFixture extends AbstractResourceFixture
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'dos_oauth_client';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode)
    {
        $resourceNode
            ->children()
            ->scalarNode('user')->cannotBeEmpty()->end()
            ->scalarNode('identifier')->cannotBeEmpty()->end()
            ->scalarNode('name')->cannotBeEmpty()->end()
            ->scalarNode('description')->cannotBeEmpty()->end()
            ->scalarNode('secret')->cannotBeEmpty()->end()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->arrayNode('grant_types')->prototype('scalar')->cannotBeEmpty()->defaultValue(['authorization_code', 'refresh_token'])->end()->end()
            ->arrayNode('redirect_uris')->prototype('scalar')->cannotBeEmpty()->defaultValue([])->end();
    }
}
