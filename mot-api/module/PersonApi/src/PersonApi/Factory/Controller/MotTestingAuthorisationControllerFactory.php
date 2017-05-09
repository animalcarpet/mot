<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\AuthorisedExaminerController;
use PersonApi\Controller\MotTestingAuthorisationController;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class MotTestingAuthorisationControllerFactory.
 *
 * Generates the MotTestingAuthorisationController, injecting dependencies
 */
class MotTestingAuthorisationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return AuthorisedExaminerController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonalAuthorisationForMotTestingService $personalAuthorisationForMotTestingService */
        $personalAuthorisationForMotTestingService = $serviceLocator->get(
            PersonalAuthorisationForMotTestingService::class
        );

        return new MotTestingAuthorisationController($personalAuthorisationForMotTestingService);
    }
}
