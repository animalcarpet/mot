<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Vehicle\CreateVehicle\Action\CountryOfRegistrationAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CountryOfRegistrationActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CountryOfRegistrationAction(
            $serviceLocator->get(CreateVehicleStepService::class),
            $serviceLocator->get(MotAuthorisationServiceInterface::class)
        );
    }
}