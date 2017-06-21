<?php

namespace AccountApiTest\Factory\Controller;

use AccountApi\Controller\PasswordUpdateController;
use AccountApi\Factory\Controller\PasswordUpdateControllerFactory;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PasswordUpdateControllerFactoryTest.
 */
class PasswordUpdateControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $tokenService = XMock::of(TokenService::class);
        $serviceManager->setService(TokenService::class, $tokenService);

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordUpdateControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordUpdateController::class, $factoryResult);
    }
}
