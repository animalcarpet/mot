<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonalDetailsController;
use PersonApi\Service\PersonalDetailsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonalDetailsControllerFactory.
 *
 * Generates the PersonalDetailsController, injecting dependencies
 */
class PersonalDetailsControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonalDetailsController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonalDetailsService $personalDetailsService */
        $personalDetailsService = $serviceLocator->get(PersonalDetailsService::class);

        return new PersonalDetailsController($personalDetailsService);
    }
}
