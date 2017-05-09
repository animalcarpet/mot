<?php

namespace DvsaCommonApi\Factory\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommonApi\Listener\ErrorHandlingListener;

class ErrorHandlingListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ErrorHandlingListener();
    }
}
