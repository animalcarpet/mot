<?php

namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Request;

class RequestFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Request;
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('Request');
    }
}
