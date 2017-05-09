<?php

namespace DvsaMotApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\CertificateChangeReasonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaMotApi\Factory\Service\ReplacementCertificateDraftUpdaterFactory;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftUpdater;
use DataCatalogApi\Service\VehicleCatalogService;
use DvsaMotApi\Service\Validator\ReplacementCertificateDraftChangeValidator;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class ReplacementCertificateDraftUpdaterFactoryTest.
 */
class ReplacementCertificateDraftUpdaterFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;

    public function setUp()
    {
        $motTestSecurityService = XMock::of(MotTestSecurityService::class);
        $authorisationService = XMock::of(AuthorisationServiceInterface::class);
        $vehicleCatalogService = XMock::of(VehicleCatalogService::class);
        $authenticationService = XMock::of(AuthenticationService::class);
        $validator = XMock::of(ReplacementCertificateDraftChangeValidator::class);

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);
        $this->serviceLocator->setService('MotTestSecurityService', $motTestSecurityService);
        $this->serviceLocator->setService('VehicleCatalogService', $vehicleCatalogService);
        $this->serviceLocator->setService('DvsaAuthenticationService', $authenticationService);
        $this->serviceLocator->setService(ReplacementCertificateDraftChangeValidator::class, $validator);

        $entityManager = XMock::of(EntityManager::class);
        $entityManager->expects($this->at(0))->method('getRepository')->willReturn(XMock::of(CertificateChangeReasonRepository::class));
        $entityManager->expects($this->at(1))->method('getRepository')->willReturn(XMock::of(SiteRepository::class));

        $this->serviceLocator->setService(EntityManager::class, $entityManager);
    }

    public function testReplacementCertificateDraftUpdaterFactory()
    {
        $service = (new ReplacementCertificateDraftUpdaterFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            ReplacementCertificateDraftUpdater::class,
            $service
        );
    }
}
