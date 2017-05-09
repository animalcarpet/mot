<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonSiteCountController;
use PersonApi\Service\PersonService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonSiteCountControllerFactory.
 *
 * Generates the PersonSiteCountController, injecting dependencies
 */
class PersonSiteCountControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonSiteCountController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonService $personService */
        $personService = $serviceLocator->get(PersonService::class);

        return new PersonSiteCountController($personService);
    }
}
