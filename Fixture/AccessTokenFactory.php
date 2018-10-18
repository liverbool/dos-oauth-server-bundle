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

use Dos\OAuthServerBundle\Model\AccessTokenInterface;
use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\ScopeInterface;
use Faker\Factory;
use PhpMob\DataFixture\ExampleFactoryInterface;
use PhpMob\DataFixture\OptionsResolver\LazyOption;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\User\Model\UserInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessTokenFactory implements ExampleFactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private $accessTokenFactory;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var RepositoryInterface
     */
    private $clientRepository;

    /**
     * @var RepositoryInterface
     */
    private $scopeRepository;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    public function __construct(
        FactoryInterface $accessTokenFactory,
        RepositoryInterface $clientRepository,
        RepositoryInterface $scopeRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->accessTokenFactory = $accessTokenFactory;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->scopeRepository = $scopeRepository;

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

        /** @var AccessTokenInterface $object */
        $object = $this->accessTokenFactory->createNew();

        $object->setIdentifier($options['identifier']);
        $object->setClient($options['client']);
        $object->setUser($options['user']);

        if (isset($options['expires_in'])) {
            $object->setExpiryDateTime($options['expires_in']);
        }

        if (isset($options['scope'])) {
            $scopes = explode(' ', trim($options['scope']));

            foreach ($scopes as $scope) {
                /** @var ScopeInterface $scopeObject */
                $scopeObject = $this->scopeRepository->findOneBy(['identifier' => $scope]);
                $object->addScope($scopeObject);
            }
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
            ->setDefault('user', LazyOption::randomOne($this->userRepository))
            ->setAllowedTypes('user', ['string', UserInterface::class, 'null'])
            ->setNormalizer('user', function (Options $options, $userEmail) {
                return $this->userRepository->findOneByEmail($userEmail);
            })
            ->setDefault('client', LazyOption::randomOne($this->clientRepository))
            ->setAllowedTypes('client', ['string', ClientInterface::class, 'null'])
            ->setNormalizer('client', LazyOption::findOneBy($this->clientRepository, 'identifier'))
            ->setDefault('expires_in', '1 month')
            ->setAllowedTypes('expires_in', ['string', \DateTimeInterface::class])
            ->setNormalizer('expires_in', function (Options $options, $value) {
                if ($value instanceof \DateTimeInterface) {
                    return $value;
                }

                return (new \DateTime())->add(\DateInterval::createFromDateString($value));
            })
            ->setDefault('scope', null)
            ->setAllowedTypes('scope', ['null', 'string']);
    }
}
