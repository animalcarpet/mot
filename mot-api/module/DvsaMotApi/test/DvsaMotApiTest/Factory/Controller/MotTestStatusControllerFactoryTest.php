<?php

namespace DvsaMotApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\MotTestStatusController;
use DvsaMotApi\Factory\Controller\MotTestStatusControllerFactory;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestStatusChangeNotificationService;
use DvsaMotApi\Service\MotTestStatusChangeService;
use Zend\ServiceManager\ServiceManager;

class MotTestStatusControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('MotTestStatusChangeService', XMock::of(MotTestStatusChangeService::class));
        $serviceManager->setService(CertificateCreationService::class, XMock::of(CertificateCreationService::class));
        $serviceManager->setService(
            MotTestStatusChangeNotificationService::class, XMock::of(MotTestStatusChangeNotificationService::class)
        );

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $this->assertInstanceOf(
            MotTestStatusController::class,
            (new MotTestStatusControllerFactory())->createService($plugins)
        );
    }
}
