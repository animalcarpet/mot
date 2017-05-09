<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use Vehicle\CreateVehicle\Action\ClassAction;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;

class ClassActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var MotAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);

        /* @var CreateVehicleStepService $createVehicleStepService */
        $createVehicleStepService = $serviceLocator->get(CreateVehicleStepService::class);

        /** @var CreateNewVehicleService $createNewVehicleService */
        $createNewVehicleService = $serviceLocator->get(CreateNewVehicleService::class);

        return new ClassAction(
            $authorisationService,
            $createVehicleStepService,
            $createNewVehicleService
        );
    }
}
