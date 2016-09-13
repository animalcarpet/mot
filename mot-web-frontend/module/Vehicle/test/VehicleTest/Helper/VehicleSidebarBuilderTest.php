<?php


namespace VehicleTest\Helper;


use DvsaCommonTest\TestUtils\XMock;
use Vehicle\Controller\VehicleController;
use Vehicle\Helper\VehicleSidebarBuilder;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;

class VehicleSidebarBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTestUrlGeneration
     */
    public function testUrlParams($searchData, $searchReturnedOneResult)
    {
        $url = XMock::of(Url::class);
        $url->expects($this->once())->method('__invoke')->willReturnCallback(function ($route, $params, $urlParams) use ($searchReturnedOneResult) {
            $this->assertEquals(VehicleController::BACK_TO_DETAIL, $urlParams['query']['backTo']);
            $this->assertEquals('vehicle/detail/history', $route);
            if ($searchReturnedOneResult) {
                $this->assertEquals(true, $urlParams['query'][VehicleController::SEARCH_RETUREND_ONE_RESULT]);
            }
        });

        $helper = new VehicleSidebarBuilder($url);

        $searchData = new Parameters($searchData);
        $helper->setSearchData($searchData);
        $helper->setObfuscatedVehicleId('123123');

        $sidebar = $helper->getSidebar();
        $this->assertInstanceOf(VehicleSidebar::class, $sidebar);
    }

    public function dataProviderTestUrlGeneration()
    {
        return [
            [[VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_SEARCH], true],
            [[VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_RESULT], false],
            [[], false],
        ];
    }
}