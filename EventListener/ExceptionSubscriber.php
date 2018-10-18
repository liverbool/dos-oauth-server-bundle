<?php

namespace Dos\OAuthServerBundle\EventListener;

use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Zend\Diactoros\Response as Psr7Response;

class ExceptionSubscriber extends ExceptionListener
{
    /**
     * @var bool
     */
    private $enableThrowException = false;

    /**
     * @param bool $enableThrowException
     */
    public function setEnableThrowException(bool $enableThrowException): void
    {
        $this->enableThrowException = $enableThrowException;
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof OAuthServerException || $this->enableThrowException) {
            return;
        }

        /*if (!$event->getRequest()->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }*/

        parent::onKernelException($event);
    }

    /**
     * {@inheritdoc}
     */
    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = array(
            'exception' => $exception,
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
            '_controller' => function (Request $request) {
                /** @var OAuthServerException $exception */
                $exception = $request->attributes->get('exception');

                // TODO: 1. how to support serializer to normalize array key case.
                // TODO: 2. Should be convert error 500 to self message `Internal server error`?
                return (new HttpFoundationFactory())->createResponse(
                    $exception->generateHttpResponse(new Psr7Response())
                );
            },
        );

        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -99), // before FosRest
        );
    }
}
