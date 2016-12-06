<?php


namespace Vehicle\CreateVehicle\Factory\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Vehicle\CreateVehicle\Action\ModelAction;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client;

class ModelActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);
        $createVehicleStepService = $serviceLocator->get(CreateVehicleStepService::class);
        $createVehicleModelService = $serviceLocator->get(CreateVehicleModelService::class);

        return new ModelAction(
            $authorisationService,
            $createVehicleStepService,
            $createVehicleModelService
        );
    }
}