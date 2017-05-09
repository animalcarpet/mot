<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Vehicle\CreateVehicle\Action\ConfirmationAction;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfirmationActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);
        $createVehicleStepService = $serviceLocator->get(CreateVehicleStepService::class);
        $createVehicleModelService = $serviceLocator->get(CreateVehicleModelService::class);

        return new ConfirmationAction(
            $authorisationService,
            $createVehicleStepService,
            $createVehicleModelService
        );
    }
}
