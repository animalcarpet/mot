<?php

namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Response;

class ResponseFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Response;
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('Response');
    }
}
