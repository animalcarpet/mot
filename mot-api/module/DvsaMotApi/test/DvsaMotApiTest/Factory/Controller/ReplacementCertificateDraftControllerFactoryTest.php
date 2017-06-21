<?php

namespace DvsaMotApiTest\Factory\Controller;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\ReplacementCertificateDraftController;
use DvsaMotApi\Factory\Controller\ReplacementCertificateDraftControllerFactory;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateService;

/**
 * Class ReplacementCertificateDraftControllerFactoryTest.
 */
class ReplacementCertificateDraftControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsReplacementCertificateDraftControllerInstance()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $serviceManager->setService('ReplacementCertificateService', XMock::of(ReplacementCertificateService::class));
        $serviceManager->setService(CertificateCreationService::class, XMock::of(CertificateCreationService::class));
        $serviceManager->setService('DvsaAuthorisationService', XMock::of(AuthorisationServiceInterface::class));
        $serviceManager->setService('MotTestService', XMock::of(MotTestService::class));

        $controllerManager = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $controllerManager->expects($this->any())
                         ->method('getServiceLocator')
                         ->will($this->returnValue($serviceManager));

        $factory = new ReplacementCertificateDraftControllerFactory();

        $this->assertInstanceOf(ReplacementCertificateDraftController::class, $factory->createService($controllerManager));
    }
}
