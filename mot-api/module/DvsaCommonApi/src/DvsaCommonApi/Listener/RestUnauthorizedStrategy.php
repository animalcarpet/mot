<?php

namespace DvsaCommonApi\Listener;

use DvsaCommon\Error\ApiErrorCodes;
use DvsaCommon\Exception\UnauthorisedException;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Http\Response as HttpResponse;

/**
 * Class RestUnauthorizedStrategy.
 */
class RestUnauthorizedStrategy extends AbstractListenerAggregate
{
    /** @var AuthenticationService $motIdentityProvider */
    private $motIdentityProvider;

    public function __construct(AuthenticationService $motIdentityProvider)
    {
        $this->motIdentityProvider = $motIdentityProvider;
    }

    public function onError(MvcEvent $event)
    {
        /** @var $exception UnauthorisedException */
        if (!($exception = $event->getParam('exception')) instanceof UnauthorisedException
            || ($result = $event->getResult()) instanceof HttpResponse
            || !($response = $event->getResponse()) instanceof HttpResponse
        ) {
            return;
        }

        /* @var Response $response */
        if ($this->motIdentityProvider->getIdentity() == null) {
            $model = new JsonModel(
                [
                    'errors' => [[
                        'message' => 'Unauthorized',
                        'code' => 401,
                    ]],
                ]
            );
            $response->setStatusCode(401);
            $response->getHeaders()->addHeaders(
                [
                    'WWW-Authenticate' => 'Bearer',
                ]
            );
        } else {
            $model = new JsonModel(
                [
                    'errors' => [[
                        'message' => 'Forbidden',
                        'code' => ApiErrorCodes::UNAUTHORISED,
                    ]],
                    'debugInfo' => $exception->getDebugInfo(),
                ]
            );
            $response->setStatusCode(403);
        }

        $event->setViewModel($model);
        $event->setResult($model);
        $event->stopPropagation();
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError']);
    }
}
