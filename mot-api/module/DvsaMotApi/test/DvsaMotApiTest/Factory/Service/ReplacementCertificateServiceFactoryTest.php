<?php

namespace DvsaMotApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\ReplacementCertificateDraftRepository;
use DvsaMotApi\Factory\Service\ReplacementCertificateServiceFactory;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftCreator;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftUpdater;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateUpdater;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ReplacementCertificateServiceFactoryTest
 *
 */
class ReplacementCertificateServiceFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;

    public function setUp()
    {
        $authorisationService = XMock::of(AuthorisationServiceInterface::class);
        $replacementCertificateDraftRepository = XMock::of(ReplacementCertificateDraftRepository::class);
        $replacementCertificateDraftCreator = XMock::of(ReplacementCertificateDraftCreator::class);
        $replacementCertificateDraftUpdater = XMock::of(ReplacementCertificateDraftUpdater::class);
        $replacementCertificateUpdater = XMock::of(ReplacementCertificateUpdater::class);
        $certificateReplacementRepository = XMock::of(CertificateReplacementRepository::class);
        $motTestRepository = XMock::of(MotTestRepository::class);
        $otpService = XMock::of(OtpService::class);
        $certificateCreationService = XMock::of(CertificateCreationService::class);

        $mockEntityManager = $this->getMockEntityManager();

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $mockEntityManager);
        $this->serviceLocator->setService(
            'ReplacementCertificateDraftRepository',
            $replacementCertificateDraftRepository
        );
        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);
        $this->serviceLocator->setService('ReplacementCertificateDraftCreator', $replacementCertificateDraftCreator);
        $this->serviceLocator->setService('ReplacementCertificateDraftUpdater', $replacementCertificateDraftUpdater);
        $this->serviceLocator->setService('ReplacementCertificateUpdater', $replacementCertificateUpdater);
        $this->serviceLocator->setService('CertificateReplacementRepository', $certificateReplacementRepository);
        $this->serviceLocator->setService(MotTestRepository::class, $motTestRepository);
        $this->serviceLocator->setService(OtpService::class, $otpService);
        $this->serviceLocator->setService(CertificateCreationService::class, $certificateCreationService);
        $this->serviceLocator->setService(MotIdentityProviderInterface::class, XMock::of(MotIdentityProviderInterface::class));
    }

    public function testReplacementCertificateServiceFactory()
    {
        $service = (new ReplacementCertificateServiceFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            ReplacementCertificateService::class,
            $service
        );
    }
}
