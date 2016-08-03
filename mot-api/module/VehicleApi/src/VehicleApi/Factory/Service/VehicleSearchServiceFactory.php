<?php

namespace VehicleApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\DataConversion\FuzzySearchConverter;
use DvsaEntities\DataConversion\SpaceStripConverter;
use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\DvlaVehicleImportChangeLog;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use VehicleApi\Service\VehicleSearchService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use Dvsa\Mot\ApiClient\Service\VehicleService;

/**
 * Create instance of service VehicleSearchService
 *
 * @package DvsaMotApi\Factory\Service
 */
class VehicleSearchServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new VehicleSearchService(
            $serviceLocator->get('DvsaAuthorisationService'),
            $entityManager->getRepository(Vehicle::class),
            $entityManager->getRepository(DvlaVehicle::class),
            $entityManager->getRepository(DvlaVehicleImportChangeLog::class),
            $entityManager->getRepository(MotTest::class),
            $serviceLocator->get('TesterService'),
            $serviceLocator->get('VehicleCatalogService'),
            $serviceLocator->get(ParamObfuscator::class),
            $serviceLocator->get(RetestEligibilityValidator::class),
            new FuzzySearchConverter(),
            new SpaceStripConverter(),
            $serviceLocator->get(VehicleService::class)
        );
    }
}
