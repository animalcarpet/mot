<?php

namespace VehicleTest\Helper;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;
use InvalidArgumentException;
use Vehicle\Controller\VehicleController;
use Vehicle\Helper\VehicleSidebarBuilder;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;

class VehicleSidebarBuilderTest extends \PHPUnit_Framework_TestCase
{
    const OBFUSCATED_VEHICLE_ID = '1w';

    /**
     * @dataProvider dataProviderTestUrlGeneration
     */
    public function testUrlParams(array $searchData, $searchReturnedOneResult)
    {
        $url = $this
            ->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $url
            ->expects($this->any())
            ->method('__invoke')
            ->willReturnCallback(function ($route, $params, $urlParams) use ($searchReturnedOneResult) {
                switch ($route) {
                    case 'vehicle/detail/mask':
                        break;
                    case 'vehicle/detail/history':
                        $this->assertEquals(VehicleController::BACK_TO_DETAIL, $urlParams['query']['backTo']);
                        if ($searchReturnedOneResult) {
                            $this->assertEquals(true, $urlParams['query'][VehicleController::SEARCH_RETUREND_ONE_RESULT]);
                        }
                        break;
                    default:
                        throw new InvalidArgumentException(sprintf('Unrecognised route "%s"', $route));
                }
            });

        $authorisationService = $this
            ->getMockBuilder(MotAuthorisationServiceInterface::class)
            ->getMock();
        $authorisationService
            ->expects($this->atLeastOnce())
            ->method('isGranted')
            ->with(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES)
            ->willReturn(true);

        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();
        $featureToggles
            ->expects($this->atLeastOnce())
            ->method('isEnabled')
            ->with(FeatureToggle::MYSTERY_SHOPPER)
            ->willReturn(true);

        $helper = new VehicleSidebarBuilder($url, $authorisationService, $featureToggles);

        $searchData = new Parameters($searchData);
        $helper->setSearchData($searchData);
        $helper->setObfuscatedVehicleId('123123');

        $vehicleSidebar = $helper->getSidebar();
        $this->assertInstanceOf(VehicleSidebar::class, $vehicleSidebar);
    }

    /**
     * @return array
     */
    public function dataProviderTestUrlGeneration()
    {
        return [
            [[VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_SEARCH], true],
            [[VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_RESULT], false],
            [[], false],
        ];
    }
}
