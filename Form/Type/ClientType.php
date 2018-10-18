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

namespace Dos\OAuthServerBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ClientType extends AbstractResourceType
{
    /**
     * @var array
     */
    private $configureGrantTypes = [];

    /**
     * @param array $configureGrantTypes
     */
    public function setConfigureGrantTypes(array $configureGrantTypes): void
    {
        $this->configureGrantTypes = $configureGrantTypes;
    }

    private function getGrantTypeChoices(): array
    {
        $grantTypes = $this->configureGrantTypes;

        array_walk($grantTypes, function(&$value) {
            $value = $value['description'];
        });

        return $grantTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'dos.form.oauth_client.translations',
                'entry_type' => ClientTranslationType::class,
            ])
            ->add('logo', ClientLogoType::class, [
                'required' => true,
                'label' => 'dos.form.oauth_client.logo',
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => true,
                'label' => 'dos.form.oauth_client.enabled',
            ])
            ->add('authorizedRequire', CheckboxType::class, [
                'required' => true,
                'label' => 'dos.form.oauth_client.authorized_require',
            ])
            ->add('redirectUris', CollectionType::class, [
                'required' => true,
                'label' => 'dos.form.oauth_client.redirect_uris',
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('grantTypes', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'label' => 'dos.form.oauth_client.grant_types',
                'choices' => array_flip($this->getGrantTypeChoices()),
            ])
            ->add('supportsScopes', ScopeChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'label' => 'dos.form.oauth_client.supports_scopes',
            ])
            ->add('user', ResourceAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'dos.form.oauth_client.user',
                'placeholder' => 'dos.form.oauth_client.user_select',
                'resource' => 'dos.oauth_user',
                'choice_name' => 'username',
                'choice_value' => 'id',
            ])
        ;
    }
}
