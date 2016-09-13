<?php


namespace VehicleTest\ViewModel;


use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\Controller\VehicleController;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;

class VehicleSidebarTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 'asdasd';

    public function testSidebar()
    {
        $urlHelperPlugin = XMock::of(Url::class);
        $urlHelperPlugin->expects($this->once())->method('__invoke')->willReturnCallback(function($route, $params, $params2){
            $this->assertEquals($params['id'], self::VEHICLE_ID);
            $this->assertEquals('vehicle/detail/history', $route);
            $this->assertContains(VehicleController::BACK_TO_DETAIL, $params2['query']);
        });

        $sidebar = new VehicleSidebar(
            $urlHelperPlugin, new Parameters([VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_DETAIL]), self::VEHICLE_ID
        );

        $linkGroupList = $sidebar->getSidebarItems();
        $this->assertCount(1, $linkGroupList);

        /** @var GeneralSidebarLinkList $linkList */
        $linkList = $linkGroupList[0];
        $this->assertEquals('Related', $linkList->getTitle());
        $links = $linkList->getLinks();
        $link = $links[0];

        $this->assertEquals('View MOT history', $link->getText());
    }
}