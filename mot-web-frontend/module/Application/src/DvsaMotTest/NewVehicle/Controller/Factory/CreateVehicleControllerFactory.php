<?php

namespace DvsaMotTest\NewVehicle\Controller\Factory;

use Application\Service\ContingencySessionManager;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\CreateVehicleFormWizard;
use DvsaMotTest\NewVehicle\Controller\CreateVehicleController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;
use Application\Service\CanTestWithoutOtpService;


class CreateVehicleControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return CreateVehicleController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $sl */
        $sl = $controllerManager->getServiceLocator();
        $authService = $sl->get('AuthorisationService');
        $request = $sl->get('Request');

        return new CreateVehicleController(
            $sl->get(CreateVehicleFormWizard::class),
            $authService,
            $request,
            $sl->get(ContingencySessionManager::class),
            $sl->get(CanTestWithoutOtpService::class)
        );
    }
}
