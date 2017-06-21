<?php

namespace PersonApiTest\Factory\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Controller\PasswordController;
use PersonApi\Factory\Controller\PasswordControllerFactory;
use PersonApi\Service\PasswordService;
use Zend\ServiceManager\ServiceManager;

class PasswordControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(PasswordService::class);
        $serviceManager->setService(PasswordService::class, $service);

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordController::class, $factoryResult);
    }
}
