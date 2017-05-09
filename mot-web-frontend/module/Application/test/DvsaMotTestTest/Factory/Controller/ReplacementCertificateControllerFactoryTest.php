<?php

namespace DvsaMotTestTest\Factory\Controller;

use DvsaCommonTest\Bootstrap;
use DvsaMotTest\Controller\ReplacementCertificateController;
use DvsaMotTest\Factory\Controller\ReplacementCertificateControllerFactory;

/**
 * Class ReplacementCertificateControllerFactoryTest.
 */
class ReplacementCertificateControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsReplacementCertificateControllerInstance()
    {
        $serviceManager = Bootstrap::getServiceManager();

        $controllerManager = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $controllerManager->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));
        $factory = new ReplacementCertificateControllerFactory();

        $this->assertInstanceOf(
            ReplacementCertificateController::class,
            $factory->createService($controllerManager)
        );
    }
}
