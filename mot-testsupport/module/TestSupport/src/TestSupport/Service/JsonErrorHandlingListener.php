<?php

namespace TestSupport\Service;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

/**
 * Originally copied form \DvsaCommonApi\Listener\ErrorHandlingListener.
 */
class JsonErrorHandlingListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $eventManager)
    {
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'handleError'], 0);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'handleError'], 0);
    }

    public function handleNotFound(MvcEvent $e)
    {
        $e->setViewModel(new JsonModel(['error' => 'not found']));
    }

    public function handleError(MvcEvent $e)
    {
        $this->verifyIsError($e);

        if ($e->getResponse()->getStatusCode() === 404) {
            // This is for incorrect URLs that don't match a ZF route.
            $this->handleNotFound($e);

            return null;
        }

        $model = $this->unknownErrorToJsonModel($e);

        $e->getResponse()->setStatusCode(Response::STATUS_CODE_500);
        $e->setResult($model);

        return $model;
    }

    private function unknownErrorToJsonModel(MvcEvent $e)
    {
        $error = $e->getError();
        $exception = $e->getParam('exception');
        $exceptionJson = [];

        if ($exception) {
            $exceptionJson = [
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'stacktrace' => $exception->getTraceAsString(),
            ];
        }

        $errorJson = [
            'message' => 'An error occurred during execution; please try again later.',
            'error' => $error,
            'exception' => $exceptionJson,
        ];
        if ($error == 'error-router-no-match') {
            $errorJson['message'] = 'Resource not found.';
        }

        return new JsonModel(['errors' => [$errorJson]]);
    }

    /**
     * @param MvcEvent $e
     *
     * @throws \LogicException
     */
    private function verifyIsError(MvcEvent $e)
    {
        $error = $e->getError();
        if (!$error) {
            throw new \LogicException('This listener is only meant to be called on errors');
        }
    }
}
