<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonCurrentMotTestController;
use PersonApi\Service\PersonService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonCurrentMotTestControllerFactory.
 *
 * Generates the PersonCurrentMotTestController, injecting dependencies
 */
class PersonCurrentMotTestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonCurrentMotTestController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonService $personService */
        $personService = $serviceLocator->get(PersonService::class);

        return new PersonCurrentMotTestController($personService);
    }
}
