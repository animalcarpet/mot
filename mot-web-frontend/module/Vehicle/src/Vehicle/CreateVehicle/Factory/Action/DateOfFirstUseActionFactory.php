<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Vehicle\CreateVehicle\Action\DateOfFirstUseAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DateOfFirstUseActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);
        $createVehichleStepService = $serviceLocator->get(CreateVehicleStepService::class);

        return new DateOfFirstUseAction($authorisationService, $createVehichleStepService);
    }
}