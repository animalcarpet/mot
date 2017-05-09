<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Vehicle\CreateVehicle\Action\MakeAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MakeActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);
        $createVehicleStepService = $serviceLocator->get(CreateVehicleStepService::class);

        return new MakeAction($authorisationService, $createVehicleStepService);
    }
}
