<?php

namespace Core\Factory;

use Core\Service\SessionService;
use DvsaClient\MapperFactory;
use Zend\Session\Container;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SessionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SessionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer = new Container(SessionService::UNIQUE_KEY);

        return new SessionService(
            $sessionContainer,
            $serviceLocator->get(MapperFactory::class)
        );
    }
}
