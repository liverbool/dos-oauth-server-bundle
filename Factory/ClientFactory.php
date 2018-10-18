<?php

namespace Dos\OAuthServerBundle\Factory;

use Dos\OAuthServerBundle\Model\ClientInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ClientFactory implements FactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private $decoratedFactory;

    public function __construct(FactoryInterface $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * @return object|ClientInterface
     */
    public function createNew()
    {
        return $this->decoratedFactory->createNew();
    }

    /**
     * @return ClientInterface
     */
    public function createWithIdentify()
    {
        $object = $this->createNew();
        $object->setIdentifier(bin2hex(random_bytes(40)));
        $object->setSecret(bin2hex(random_bytes(40)));

        return $object;
    }
}
