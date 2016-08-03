<?php

namespace DvsaMotApi\Factory\Service;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\VehicleHistoryService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class VehicleHistoryServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new VehicleHistoryService(
            $serviceLocator->get(MotTestRepository::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('ConfigurationRepository')
        );
    }
}
