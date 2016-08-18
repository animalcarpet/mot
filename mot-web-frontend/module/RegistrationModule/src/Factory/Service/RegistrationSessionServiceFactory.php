<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Factory\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use DvsaClient\MapperFactory;
use Session\Container;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegistrationSessionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RegistrationSessionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer =  new Container(RegistrationSessionService::UNIQUE_KEY);

        return new RegistrationSessionService(
            $sessionContainer,
            $serviceLocator->get(MapperFactory::class)
        );
    }
}
