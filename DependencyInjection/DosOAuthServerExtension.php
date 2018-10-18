<?php

namespace Dos\OAuthServerBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class DosOAuthServerExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'dos_oauth_server';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('dos.oauth.private_key', $config['private_key']);
        $container->setParameter('dos.oauth.public_key', $config['public_key']);
        $container->setParameter('dos.oauth.key_permissions_check', $config['key_permissions_check']);
        $container->setParameter('dos.oauth.pass_phrase', $config['pass_phrase']);
        $container->setParameter('dos.oauth.encryption_key', $config['encryption_key']);
        $container->setParameter('dos.oauth.access_token_ttl', $config['access_token_ttl']);
        $container->setParameter('dos.oauth.authorization_code_ttl', $config['authorization_code_ttl']);
        $container->setParameter('dos.oauth.implicit_token_ttl', $config['implicit_token_ttl']);
        $container->setParameter('dos.oauth.client_grant_restrict', $config['client_grant_restrict']);
        $container->setParameter('dos.oauth.client_scope_restrict', $config['client_scope_restrict']);
        $container->setParameter('dos.oauth.throw_exception', $config['throw_exception']);

        $grantTypes = [];

        foreach ($config['grant_types'] as $id => $grantType) {
            $grantTypes[$id] = $grantType;
        }

        $container->setParameter('dos.oauth.grant_types', $grantTypes);

        $this->registerResources('dos', $config['driver'], $config['resources'], $container);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if ($config['enable_fixture']) {
            $loader->load('services/fixture.xml');
        }

        $loader->load('services.xml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $validRequirement = false;
        $config = $container->getExtensionConfig('security');
        $config = array_replace_recursive([
            'providers' => [
                'dos_oauth' => [
                    'id' => 'dos.oauth.user_provider',
                ],
            ]
        ], $config[0]);

        unset($config['access_control']);

        foreach($config['firewalls'] as $key => &$value) {
            if (array_key_exists('provider', $value) && $value['provider'] === 'dos_oauth') {
                $value['simple_preauth'] = ['authenticator' => 'dos.oauth.authenticator'];
                $validRequirement = true;
            }
        }

        if (false === $validRequirement) {
            throw new \RuntimeException("You must to config a `dos_oauth` as provider to your firewall.");
        }

        $container->prependExtensionConfig('security', $config);
    }
}
