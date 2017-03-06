<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaEntityTest\Helper;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Factory\Helper\MysteryShopperHelperFactory;
use DvsaMotApi\Helper\MysteryShopperHelper;

/**
 * Class MysteryShopperHelperTest.
 */
class MysteryShopperHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MysteryShopperHelper
     */
    private $mysteryShopperHelper;

    /**
     * @var DvsaVehicle
     */
    private $mockDvsaVehicle;

    /**
     * @var VehicleService
     */
    private $mockVehicleService;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $mockAuthorisationService;

    public function setup()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $this->mockDvsaVehicle = $this
            ->getMockBuilder(DvsaVehicle::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockVehicleService = XMock::of(VehicleService::class);
        $this->mockVehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleById')
            ->willReturn($this->mockDvsaVehicle);

        $this->mockAuthorisationService = XMock::of(MotAuthorisationServiceInterface::class);

        $serviceManager->setService(VehicleService::class, $this->mockVehicleService);
        $serviceManager->setService(MotAuthorisationServiceInterface::class, $this->mockAuthorisationService);

        $this->mysteryShopperHelper = (new MysteryShopperHelperFactory())->createService($serviceManager);
    }

    public function testMysteryShopperHelperIsIncognitoTrue()
    {
        $this->setVehicleIsIncognito(true);
        $expectedIsMysteryShopper = true;
        $actual = $this->mysteryShopperHelper->isVehicleMysteryShopper(0);

        $this->assertEquals($expectedIsMysteryShopper, $actual);
    }

    public function testMysteryShopperHelperIsIncognitoFalse()
    {
        $this->setVehicleIsIncognito(false);
        $expectedIsMysteryShopper = false;
        $actual = $this->mysteryShopperHelper->isVehicleMysteryShopper(0);

        $this->assertEquals($expectedIsMysteryShopper, $actual);
    }

    public function testCanViewMysteryShopperTestsWhenToggleOnAndPermissionGranted()
    {
        $this->setPermission(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS, true);

        $this->assertTrue($this->mysteryShopperHelper->hasPermissionToViewMysteryShopperTests());
    }

    public function testCanNotViewMysteryShopperTestsWhenToggleOnAndPermissionNotGranted()
    {
        $this->setPermission(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS, false);

        $this->assertFalse($this->mysteryShopperHelper->hasPermissionToViewMysteryShopperTests());
    }

    private function setPermission($permission, $isActive)
    {
        $this->mockAuthorisationService
            ->expects($this->any())
            ->method('isGranted')
            ->with($permission)
            ->willReturn($isActive);
    }

    private function setVehicleIsIncognito($boolean)
    {
        $this->mockDvsaVehicle
            ->expects($this->any())
            ->method('getIsIncognito')
            ->willReturn($boolean);
    }
}
