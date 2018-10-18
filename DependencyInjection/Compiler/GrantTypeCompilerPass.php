<?php


namespace Dos\OAuthServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GrantTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('dos.oauth.authorization_server');

        foreach ($container->getParameter('dos.oauth.grant_types') as $value) {
            if ($value['enabled']) {
                $definition->addMethodCall('enableGrantType', [
                    new Reference($value['service']),
                    new Definition(\DateInterval::class, [$container->getParameter('dos.oauth.access_token_ttl')])
                ]);
            }
        }
    }
}
