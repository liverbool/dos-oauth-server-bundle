<?php

namespace Dos\OAuthServerBundle\DependencyInjection;

use Dos\OAuthServerBundle\Form\Type\AccessTokenType;
use Dos\OAuthServerBundle\Form\Type\AuthorizationCodeType;
use Dos\OAuthServerBundle\Form\Type\ClientLogoType;
use Dos\OAuthServerBundle\Form\Type\ClientTranslationType;
use Dos\OAuthServerBundle\Form\Type\ClientType;
use Dos\OAuthServerBundle\Form\Type\RefreshTokenType;
use Dos\OAuthServerBundle\Form\Type\ScopeTranslationType;
use Dos\OAuthServerBundle\Form\Type\ScopeType;
use Dos\OAuthServerBundle\Model\AccessToken;
use Dos\OAuthServerBundle\Model\AccessTokenInterface;
use Dos\OAuthServerBundle\Model\AuthorizationCode;
use Dos\OAuthServerBundle\Model\AuthorizationCodeInterface;
use Dos\OAuthServerBundle\Model\AuthorizedUser;
use Dos\OAuthServerBundle\Model\AuthorizedUserInterface;
use Dos\OAuthServerBundle\Model\Client;
use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\ClientLogo;
use Dos\OAuthServerBundle\Model\ClientLogoInterface;
use Dos\OAuthServerBundle\Model\ClientTranslation;
use Dos\OAuthServerBundle\Model\ClientTranslationInterface;
use Dos\OAuthServerBundle\Model\RefreshToken;
use Dos\OAuthServerBundle\Model\RefreshTokenInterface;
use Dos\OAuthServerBundle\Model\Scope;
use Dos\OAuthServerBundle\Model\ScopeInterface;
use Dos\OAuthServerBundle\Model\ScopeTranslation;
use Dos\OAuthServerBundle\Model\ScopeTranslationInterface;
use Dos\OAuthServerBundle\Model\UserInterface;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\AccessTokenRepository;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\AuthorizationCodeRepository;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\AuthorizedUserRepository;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\ClientRepository;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\RefreshTokenRepository;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\ScopeRepository;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\UserRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Component\Resource\Factory\TranslatableFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dos_oauth_server');

        $rootNode
            ->children()
                ->scalarNode('driver')->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)->end()
                ->scalarNode('enable_fixture')->defaultTrue()->end()
                ->scalarNode('private_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('public_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('pass_phrase')->defaultNull()->end()
                ->booleanNode('key_permissions_check')->defaultFalse()->end()
                ->scalarNode('encryption_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('throw_exception')->defaultFalse()->cannotBeEmpty()->end()
            ->end()
        ;

        $this->addGrantTypeSection($rootNode);
        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addGrantTypeSection(ArrayNodeDefinition $node): void
    {
        $node
            ->beforeNormalization()
                ->always(function ($value) {
                    $defaultGrantTypes = [
                        'authorization_code' => [
                            'service' => 'dos.oauth.authorization_code_grant',
                            'description' => 'dos.ui.authorization_code_grant',
                            'enabled' => true,
                        ],
                        'client_credentials' => [
                            'service' => 'dos.oauth.client_credentials_grant',
                            'description' => 'dos.ui.client_credentials_grant',
                            'enabled' => true,
                        ],
                        'implicit' => [
                            'service' => 'dos.oauth.implicit_grant',
                            'description' => 'dos.ui.implicit_grant',
                            'enabled' => true,
                        ],
                        'password' => [
                            'service' => 'dos.oauth.password_grant',
                            'description' => 'dos.ui.password_grant',
                            'enabled' => true,
                        ],
                        'refresh_token' => [
                            'service' => 'dos.oauth.refresh_token_grant',
                            'description' => 'dos.ui.refresh_token_grant',
                            'enabled' => true,
                        ],
                    ];

                    return array_replace_recursive(['grant_types' => $defaultGrantTypes], $value);
                })
            ->end()
            ->children()
                ->booleanNode('client_grant_restrict')->defaultValue(false)->end()
                ->booleanNode('client_scope_restrict')->defaultValue(false)->end()
                ->scalarNode('access_token_ttl')->defaultValue('PT1H')->end()
                ->scalarNode('authorization_code_ttl')->defaultValue('PT5M')->end()
                ->scalarNode('implicit_token_ttl')->defaultValue('PT1H')->end()
                ->arrayNode('grant_types')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('service')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('description')->isRequired()->cannotBeEmpty()->end()
                            ->booleanNode('enabled')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('oauth_access_token')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(AccessToken::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(AccessTokenInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(AccessTokenRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(AccessTokenType::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_authorized_user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(AuthorizedUser::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(AuthorizedUserInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(AuthorizedUserRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_authorization_code')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(AuthorizationCode::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(AuthorizationCodeInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(AuthorizationCodeRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(AuthorizationCodeType::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_client')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Client::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(ClientInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(ClientRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(TranslatableFactory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(ClientType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->variableNode('options')->end()
                                        ->arrayNode('classes')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->defaultValue(ClientTranslation::class)->cannotBeEmpty()->end()
                                                ->scalarNode('interface')->defaultValue(ClientTranslationInterface::class)->cannotBeEmpty()->end()
                                                ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                                ->scalarNode('repository')->cannotBeEmpty()->end()
                                                ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                                ->scalarNode('form')->defaultValue(ClientTranslationType::class)->cannotBeEmpty()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_client_logo')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(ClientLogo::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(ClientLogoInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(ClientLogoType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_refresh_token')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(RefreshToken::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(RefreshTokenInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(RefreshTokenRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(RefreshTokenType::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_scope')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Scope::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(ScopeInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(ScopeRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(TranslatableFactory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(ScopeType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->variableNode('options')->end()
                                        ->arrayNode('classes')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->defaultValue(ScopeTranslation::class)->cannotBeEmpty()->end()
                                                ->scalarNode('interface')->defaultValue(ScopeTranslationInterface::class)->cannotBeEmpty()->end()
                                                ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                                ->scalarNode('repository')->cannotBeEmpty()->end()
                                                ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                                ->scalarNode('form')->defaultValue(ScopeTranslationType::class)->cannotBeEmpty()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('oauth_user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(UserRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(UserInterface::class)->cannotBeOverwritten()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
