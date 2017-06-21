<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use VehicleApi\Controller\MysteryShopperVehicleController;
use VehicleApi\Factory\Controller\MysteryShopperVehicleControllerFactory;
use VehicleApi\Service\MysteryShopperVehicleService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * class MysteryShopperVehicleControllerFactory.
 */
class MysteryShopperVehicleControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        /** @var ServiceManager $serviceManager */
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            MysteryShopperVehicleService::class,
            XMock::of(MysteryShopperVehicleService::class)
        );

        /** @var ControllerManager $plugins */
        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        /** @var MysteryShopperVehicleControllerFactory $factory */
        $factory = new MysteryShopperVehicleControllerFactory();

        $this->assertInstanceOf(
            MysteryShopperVehicleController::class,
            $factory->createService($plugins)
        );
    }
}
