<?php

namespace DvsaMotApiTest\Factory;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaMotApi\Factory\Service\CertificateExpiryServiceFactory;
use DvsaMotApi\Service\CertificateExpiryService;
use Zend\ServiceManager\ServiceLocatorInterface;

class CertificateExpiryServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(MotTestRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(VehicleRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(ConfigurationRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(AuthorisationService::class));

        $this->assertInstanceOf(
            CertificateExpiryService::class,
            (new CertificateExpiryServiceFactory())->createService($mockServiceLocator)
        );
    }
}
