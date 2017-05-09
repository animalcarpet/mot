<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonEventController;
use PersonApi\Service\PersonEventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonEventControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PersonEventController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new PersonEventController(
            $serviceLocator->get(PersonEventService::class)
        );
    }
}
