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

use Dos\OAuthServerBundle\Model\ScopeInterface;
use Faker\Factory;
use PhpMob\DataFixture\ExampleFactoryInterface;
use PhpMob\DataFixture\LocaleAwareFactoryTrait;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScopeFactory implements ExampleFactoryInterface
{
    use LocaleAwareFactoryTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;

        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): ScopeInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var ScopeInterface $scope */
        $scope = $this->factory->createNew();

        $scope->setIdentifier($options['identifier']);

        $this->setLocalizedData($scope, ['name', 'description'], $options);

        return $scope;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('identifier', function (Options $options) {
                return $this->faker->unique()->randomNumber(8);
            })
            ->setDefault('name', function (Options $options) {
                return $this->faker->unique()->word;
            })
            ->setDefault('description', function (Options $options) {
                return $this->faker->words(3, true);
            })
        ;
    }
}
