<?php

namespace AccountApiTest\Factory\Controller;

use AccountApi\Controller\PasswordResetController;
use AccountApi\Factory\Controller\PasswordResetControllerFactory;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PasswordResetControllerFactoryTest.
 */
class PasswordResetControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $tokenService = XMock::of(TokenService::class);
        $serviceManager->setService(TokenService::class, $tokenService);

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordResetControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordResetController::class, $factoryResult);
    }
}
