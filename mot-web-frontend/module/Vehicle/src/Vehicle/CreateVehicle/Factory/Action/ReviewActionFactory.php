<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Vehicle\CreateVehicle\Action\ReviewAction;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReviewActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ReviewAction(
            $serviceLocator->get(MotAuthorisationServiceInterface::class),
            $serviceLocator->get(CreateVehicleStepService::class),
            $serviceLocator->get(CreateVehicleModelService::class),
            $serviceLocator->get(CreateNewVehicleService::class)
        );
    }
}
