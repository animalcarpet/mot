<?php

namespace VehicleApiTest\Factory\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\DvlaVehicleImportChangesRepository;
use DvsaEntities\Repository\DvlaVehicleRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaEntities\Repository\VehicleV5CRepository;
use DvsaMotApi\Service\MotTestServiceProvider;
use DvsaAuthentication\Service\OtpService;
use VehicleApi\Factory\Service\VehicleServiceFactory;
use VehicleApi\Service\VehicleService;
use Zend\ServiceManager\ServiceLocatorInterface;

class VehicleServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $entityManager = XMock::of(EntityManager::class);
        $entityManager->expects($this->any())
                        ->method('getUnitOfWork')
                    ->willReturn(XMock::of(UnitOfWork::class));

        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(VehicleRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(VehicleV5CRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(2), XMock::of(DvlaVehicleRepository::class));
        $this->mockMethod(
            $entityManager, 'getRepository', $this->at(3), XMock::of(DvlaVehicleImportChangesRepository::class)
        );
        $this->mockMethod($entityManager, 'getRepository', $this->at(4), XMock::of(EntityRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(5), XMock::of(PersonRepository::class));

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(VehicleCatalogService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(ParamObfuscator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(MotIdentityProviderInterface::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(NewVehicleService::class));

        $this->assertInstanceOf(
            VehicleService::class,
            (new VehicleServiceFactory())->createService($mockServiceLocator)
        );
    }
}
