<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonContactController;
use PersonApi\Controller\PersonController;
use PersonApi\Service\PersonContactService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonContactControllerFactory.
 *
 * Generates the PersonContactController, injecting dependencies
 */
class PersonContactControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonContactService $personContactService */
        $personContactService = $serviceLocator->get(PersonContactService::class);

        return new PersonContactController($personContactService);
    }
}
