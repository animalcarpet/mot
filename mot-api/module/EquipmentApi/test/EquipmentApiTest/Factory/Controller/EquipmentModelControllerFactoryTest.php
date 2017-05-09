<?php

namespace EquipmentApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use EquipmentApi\Controller\EquipmentModelController;
use EquipmentApi\Factory\Controller\EquipmentModelControllerFactory;
use EquipmentApi\Mapper\EquipmentModelMapper;
use EquipmentApi\Service\EquipmentModelService;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PasswordChangeControllerFactoryTest.
 */
class EquipmentModelControllerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $equipmentModelService = XMock::of(EquipmentModelService::class);
        $equipmentModelMapper = XMock::of(EquipmentModelMapper::class);
        $serviceManager->setService(EquipmentModelService::class, $equipmentModelService);
        $serviceManager->setService(EquipmentModelMapper::class, $equipmentModelMapper);
        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new EquipmentModelControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(EquipmentModelController::class, $factoryResult);
    }
}
