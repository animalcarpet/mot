<?php

namespace DvsaMotApiTest\Factory\Service;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Factory\Service\ReplacementCertificateUpdaterFactory;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\CertificateOdometerHistoryUpdater;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateUpdater;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ReplacementCertificateUpdaterFactoryTest.
 */
class ReplacementCertificateUpdaterFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;

    public function setUp()
    {
        $motTestSecurityService = XMock::of(MotTestSecurityService::class);
        $authorisationService = XMock::of(AuthorisationServiceInterface::class);
        $authenticationService = XMock::of(AuthenticationService::class);
        $mockVehicleService = XMock::of(VehicleService::class);
        $mockMotTestRepository = XMock::of(MotTestRepository::class);
        $mockCertificateOdometerHistoryUpdater = XMock::of(CertificateOdometerHistoryUpdater::class);

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);
        $this->serviceLocator->setService('MotTestSecurityService', $motTestSecurityService);
        $this->serviceLocator->setService('DvsaAuthenticationService', $authenticationService);
        $this->serviceLocator->setService(VehicleService::class, $mockVehicleService);
        $this->serviceLocator->setService(MotTestRepository::class, $mockMotTestRepository);
        $this->serviceLocator->setService(CertificateOdometerHistoryUpdater::class, $mockCertificateOdometerHistoryUpdater);
    }

    public function testReplacementCertificateServiceFactory()
    {
        $service = (new ReplacementCertificateUpdaterFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            ReplacementCertificateUpdater::class,
            $service
        );
    }
}
