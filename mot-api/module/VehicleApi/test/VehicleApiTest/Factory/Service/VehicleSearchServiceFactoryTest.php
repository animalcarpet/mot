<?php

namespace VehicleApiTest\Factory\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\DvlaVehicleImportChangesRepository;
use DvsaEntities\Repository\DvlaVehicleRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use VehicleApi\Service\VehicleSearchService;
use VehicleApi\Factory\Service\VehicleSearchServiceFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaMotApi\Service\TesterService;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;

class VehicleSearchServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(VehicleRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(DvlaVehicleRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(2), XMock::of(DvlaVehicleImportChangesRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(3), XMock::of(MotTestRepository::class));

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(TesterService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(VehicleCatalogService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(ParamObfuscator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(RetestEligibilityValidator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(6), XMock::of(NewVehicleService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(7), XMock::of(MysteryShopperHelper::class));

        $this->assertInstanceOf(
            VehicleSearchService::class,
            (new VehicleSearchServiceFactory())->createService($mockServiceLocator)
        );
    }



}
