<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Factory\Controller;

use Dvsa\Mot\Api\RegistrationModule\Controller\RegistrationController;
use Dvsa\Mot\Api\RegistrationModule\Service\DuplicatedEmailChecker;
use Dvsa\Mot\Api\RegistrationModule\Service\RegistrationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Create instance of service RegistrationController.
 *
 * class RegistrationControllerFactory
 */
class RegistrationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return RegistrationController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var RegistrationService $registrationService */
        $registrationService = $serviceLocator->get(RegistrationService::class);

        $controller = new RegistrationController(
            $registrationService
        );

        return $controller;
    }
}
