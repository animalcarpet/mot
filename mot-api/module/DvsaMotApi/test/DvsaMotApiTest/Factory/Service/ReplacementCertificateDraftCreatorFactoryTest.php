<?php

namespace DvsaCatalogApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Factory\Service\ReplacementCertificateDraftCreatorFactory;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftCreator;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class ReplacementCertificateDraftCreatorFactoryTest.
 */
class ReplacementCertificateDraftCreatorFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;

    public function setUp()
    {
        $motTestSecurityService = XMock::of(MotTestSecurityService::class);
        $authorisationService = XMock::of(AuthorisationServiceInterface::class);
        $vehicleService = XMock::of(VehicleService::class);
        $entityManager = XMock::of(EntityManager::class);

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);
        $this->serviceLocator->setService('MotTestSecurityService', $motTestSecurityService);
        $this->serviceLocator->setService(VehicleService::class, $vehicleService);
        $this->serviceLocator->setService(EntityManager::class, $entityManager);
    }

    public function testReplacementCertificateDraftCreatorFactory()
    {
        $service = (new ReplacementCertificateDraftCreatorFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            ReplacementCertificateDraftCreator::class,
            $service
        );
    }
}
