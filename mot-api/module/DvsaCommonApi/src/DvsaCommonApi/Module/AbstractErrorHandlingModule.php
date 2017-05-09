<?php

namespace DvsaCommonApi\Module;

use DvsaCommonApi\Service\Exception\ServiceException;
use Zend\Log\Filter\Priority;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use Zend\View\Model\JsonModel;

/**
 * Class AbstractErrorHandlingModule.
 */
abstract class AbstractErrorHandlingModule
{
    public function attachJsonErrorHandling(EventManager $eventManager)
    {
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError'], 1);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'onRenderError'], 1);
    }

    public function onDispatchError($e)
    {
        return $this->getJsonModelError($e);
    }

    public function onRenderError($e)
    {
        return $this->getJsonModelError($e);
    }

    protected function getJsonModelError($e)
    {
        $error = $e->getError();
        if (!$error) {
            return;
        }

        $exception = $e->getParam('exception');

        $isServiceException = false;

        $exceptionJson = [];
        if ($exception) {
            $isServiceException = $exception instanceof ServiceException;
            if (!$isServiceException) {
                $exceptionJson = [
                    'class' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'message' => $exception->getMessage(),
                    'stacktrace' => $exception->getTraceAsString(),
                ];
            }
        }

        $model = null;

        if ($isServiceException) {
            $e->getResponse()->setStatusCode($exception->getCode());
            $model = $exception->getJsonModel();
        } else {
            $errorJson = [
                'message' => 'An error occurred during execution; please try again later.',
                'error' => $error,
                'exception' => $exceptionJson,
            ];
            if ($error == 'error-router-no-match') {
                $errorJson['message'] = 'Resource not found.';
            }

            $model = new JsonModel(['errors' => [$errorJson]]);
        }

        $e->setResult($model);

        return $model;
    }

    protected function createLogger()
    {
        return function ($serviceManager) {
            $logger = new Logger();

            $config = $serviceManager->get('config');
            if (array_key_exists('logPath', $config)) {
                $logPath = $config['logPath'];
                $writer = new Stream($logPath);

                $logLevel = Logger::WARN;
                if (array_key_exists('logLevel', $config)) {
                    $logLevel = $config['logLevel'];
                }
                $filter = new Priority($logLevel);
                $writer->addFilter($filter);

                $logger->addWriter($writer);
            } else {
                $writer = new Stream('php://output');
                $filter = new Priority(Logger::EMERG);
                $writer->addFilter($filter);
                $logger->addWriter($writer);
            }
            Logger::registerErrorHandler($logger);

            return $logger;
        };
    }
}
