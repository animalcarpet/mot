<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaEntityTest\Helper;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
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
     * @var FeatureToggles
     */
    private $mockMysteryShopperToggle;

    /**
     * @var DvsaVehicle
     */
    private $mockDvsaVehicle;

    /**
     * @var VehicleService
     */
    private $mockVehicleService;

    public function setup()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $this->mockMysteryShopperToggle = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDvsaVehicle = $this
            ->getMockBuilder(DvsaVehicle::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockVehicleService = XMock::of(VehicleService::class);
        $this->mockVehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleById')
            ->willReturn($this->mockDvsaVehicle);

        $serviceManager->setService('Feature\FeatureToggles', $this->mockMysteryShopperToggle);
        $serviceManager->setService(VehicleService::class, $this->mockVehicleService);

        $this->mysteryShopperHelper = (new MysteryShopperHelperFactory())->createService($serviceManager);
    }

    public function testMysteryShopperHelperToggleTrue()
    {
        $this->enableMysteryShopperToggle(true);
        $this->setVehicleIsIncognito(true);
        $expectedIsMysteryShopper = true;
        $actual = $this->mysteryShopperHelper->isVehicleMysteryShopper(0);

        $this->assertEquals($expectedIsMysteryShopper, $actual);
    }

    public function testMysteryShopperHelperToggleFalse()
    {
        $this->enableMysteryShopperToggle(false);
        $this->setVehicleIsIncognito(true);
        $expectedIsMysteryShopper = false;
        $actual = $this->mysteryShopperHelper->isVehicleMysteryShopper(0);

        $this->assertEquals($expectedIsMysteryShopper, $actual);
    }

    public function testMysteryShopperHelperIsIncognitoFalse()
    {
        $this->enableMysteryShopperToggle(true);
        $this->setVehicleIsIncognito(false);
        $expectedIsMysteryShopper = false;
        $actual = $this->mysteryShopperHelper->isVehicleMysteryShopper(0);

        $this->assertEquals($expectedIsMysteryShopper, $actual);
    }

    public function testMysteryShopperHelperIsIncognitoFalseToggleFalse()
    {
        $this->enableMysteryShopperToggle(false);
        $this->setVehicleIsIncognito(false);
        $expectedIsMysteryShopper = false;
        $actual = $this->mysteryShopperHelper->isVehicleMysteryShopper(0);

        $this->assertEquals($expectedIsMysteryShopper, $actual);
    }

    private function enableMysteryShopperToggle($boolean)
    {
        $this->mockMysteryShopperToggle
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::MYSTERY_SHOPPER)
            ->willReturn($boolean);
    }

    private function setVehicleIsIncognito($boolean)
    {
        $this->mockDvsaVehicle
            ->expects($this->any())
            ->method('getIsIncognito')
            ->willReturn($boolean);
    }
}
