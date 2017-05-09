<?php

namespace Vehicle\CreateVehicle\Factory\Service;

use Application\Service\ContingencySessionManager;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaMotTest\Service\AuthorisedClassesService;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client;

class CreateNewVehicleServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CreateNewVehicleService(
            $serviceLocator->get(VehicleService::class),
            $serviceLocator->get(CreateVehicleStepService::class),
            $serviceLocator->get(MotFrontendIdentityProviderInterface::class),
            $serviceLocator->get(ContingencySessionManager::class),
            $serviceLocator->get(Client::class),
            $serviceLocator->get(AuthorisedClassesService::class)
        );
    }
}
