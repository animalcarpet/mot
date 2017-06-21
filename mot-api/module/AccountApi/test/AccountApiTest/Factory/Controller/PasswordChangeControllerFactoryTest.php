<?php

namespace AccountApiTest\Factory\Controller;

use AccountApi\Controller\PasswordChangeController;
use AccountApi\Factory\Controller\PasswordChangeControllerFactory;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PasswordChangeControllerFactoryTest.
 */
class PasswordChangeControllerFactoryTest extends \PHPUnit_Framework_TestCase
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
        $factory = new PasswordChangeControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordChangeController::class, $factoryResult);
    }
}
