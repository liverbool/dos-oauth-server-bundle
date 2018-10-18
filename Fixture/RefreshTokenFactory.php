<?php

/*
 * This file is part of the Dos package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dos\OAuthServerBundle\Fixture;

use Dos\OAuthServerBundle\Model\AccessTokenInterface;
use Dos\OAuthServerBundle\Model\RefreshTokenInterface;
use Faker\Factory;
use PhpMob\DataFixture\ExampleFactoryInterface;
use PhpMob\DataFixture\OptionsResolver\LazyOption;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RefreshTokenFactory implements ExampleFactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private $refreshTokenFactory;

    /**
     * @var RepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    public function __construct(
        FactoryInterface $refreshTokenFactory,
        RepositoryInterface $accessTokenRepository
    )
    {
        $this->refreshTokenFactory = $refreshTokenFactory;
        $this->accessTokenRepository = $accessTokenRepository;

        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var RefreshTokenInterface $object */
        $object = $this->refreshTokenFactory->createNew();

        $object->setAccessToken($options['access_token']);
        $object->setIdentifier($options['identifier']);

        if (isset($options['expires_in'])) {
            $object->setExpiryDateTime($options['expires_in']);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('identifier', function (Options $options) {
                return $this->faker->md5;
            })
            ->setDefault('access_token', LazyOption::randomOne($this->accessTokenRepository))
            ->setAllowedTypes('access_token', ['string', AccessTokenInterface::class, 'null'])
            ->setNormalizer('access_token', LazyOption::findOneBy($this->accessTokenRepository, 'identifier'))
            ->setDefault('expires_in', '1 month')
            ->setAllowedTypes('expires_in', ['string', \DateTimeInterface::class])
            ->setNormalizer('expires_in', function (Options $options, $value) {
                if ($value instanceof \DateTimeInterface) {
                    return $value;
                }

                return (new \DateTime())->add(\DateInterval::createFromDateString($value));
            });
    }
}
