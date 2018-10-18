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

use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\UserInterface;
use Faker\Factory;
use PhpMob\DataFixture\ExampleFactoryInterface;
use PhpMob\DataFixture\LocaleAwareFactoryTrait;
use PhpMob\DataFixture\OptionsResolver\LazyOption;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientFactory implements ExampleFactoryInterface
{
    use LocaleAwareFactoryTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    public function __construct(FactoryInterface $factory, UserRepositoryInterface $userRepository)
    {
        $this->factory = $factory;
        $this->userRepository = $userRepository;

        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): ClientInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var ClientInterface $client */
        $client = $this->factory->createNew();

        $client->setIdentifier($options['identifier']);
        $client->setUser($options['user']);
        $client->setEnabled($options['enabled']);
        $client->setSecret($options['secret']);
        $client->setGrantTypes($options['grant_types']);
        $client->setRedirectUris($options['redirect_uris']);

        $this->setLocalizedData($client, ['name', 'description'], $options);

        return $client;
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
            ->setDefault('user', LazyOption::randomOne($this->userRepository))
            ->setAllowedTypes('user', ['string', UserInterface::class, 'null'])
            ->setNormalizer('user', function (Options $options, $userEmail) {
                return $this->userRepository->findOneByEmail($userEmail);
            })
            ->setDefault('name', function (Options $options) {
                return $this->faker->unique()->word;
            })
            ->setDefault('description', function (Options $options) {
                return $this->faker->text;
            })
            ->setDefault('secret', function (Options $options) {
                return $this->faker->uuid;
            })
            ->setDefault('enabled', true)
            ->setDefault('grant_types', [])
            ->setAllowedTypes('grant_types', ['array'])
            ->setDefault('redirect_uris', [])
            ->setAllowedTypes('redirect_uris', ['array'])
        ;
    }
}
