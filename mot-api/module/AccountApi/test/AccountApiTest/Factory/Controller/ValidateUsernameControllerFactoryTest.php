<?php

namespace AccountApiTest\Factory\Controller;

use AccountApi\Controller\ValidateUsernameController;
use AccountApi\Factory\Controller\ValidateUsernameControllerFactory;
use PersonApi\Service\PersonService;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ValidateUsernameControllerFactoryTest.
 */
class ValidateUsernameControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(PersonService::class);
        $serviceManager->setService(PersonService::class, $service);

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new ValidateUsernameControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(ValidateUsernameController::class, $factoryResult);
    }
}
