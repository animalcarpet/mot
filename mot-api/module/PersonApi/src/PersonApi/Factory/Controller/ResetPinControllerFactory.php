<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\ResetPinController;
use PersonApi\Service\PersonService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ResetPinControllerFactory.
 *
 * Generates the ResetPinController, injecting dependencies
 */
class ResetPinControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return ResetPinController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonService $personService */
        $personService = $serviceLocator->get(PersonService::class);

        return new ResetPinController($personService);
    }
}
