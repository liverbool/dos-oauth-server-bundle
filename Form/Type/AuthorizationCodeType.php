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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class AuthorizationCodeType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identifier', TextType::class, [
                'required' => true,
                'label' => 'Identifier',
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => true,
                'label' => 'Enabled?',
            ])
            ->add('expiryDateTime', DateTimeType::class, [
                'required' => true,
                //'widget' => 'single_text',
                'label' => 'Expiry date time',
            ])
            ->add('redirectUri', UrlType::class, [
                'required' => true,
                'label' => 'Redirect uri',
            ])
            ->add('scopes', ScopeChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'choice_value' => 'identifier',
                'label' => 'Scopes',
            ])
            ->add('user', ResourceAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'User',
                'placeholder' => 'Search user ...',
                'resource' => 'dos.oauth_user',
                'choice_name' => 'username',
                'choice_value' => 'id',
            ])
            ->add('client', ResourceAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'Client',
                'placeholder' => 'Search client ...',
                'resource' => 'dos.oauth_client',
                'choice_name' => 'name',
                'choice_value' => 'id',
            ]);
    }
}
