<?php

namespace VehicleTest\ViewModel;

use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Vehicle\Controller\VehicleController;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;

class VehicleSidebarTest extends PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 'asdasd';

    public function testSidebarWithoutMaskButton()
    {
        $urlHelperPlugin = XMock::of(Url::class);
        $urlHelperPlugin->expects($this->once())->method('__invoke')->willReturnCallback(function ($route, $params, $params2) {
            $this->assertEquals($params['id'], self::VEHICLE_ID);
            $this->assertEquals('vehicle/detail/history', $route);
            $this->assertContains(VehicleController::BACK_TO_DETAIL, $params2['query']);
        });

        $searchParameters = new Parameters([VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_DETAIL]);

        $authorisationService = $this
            ->getMockBuilder(MotAuthorisationServiceInterface::class)
            ->getMock();
        $authorisationService
            ->expects($this->any())
            ->method('isGranted')
            ->with(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES)
            ->willReturn(true);

        $sidebar = new VehicleSidebar($urlHelperPlugin, $searchParameters, self::VEHICLE_ID, true, $authorisationService);

        $linkGroupList = $sidebar->getSidebarItems();

        $this->assertCount(1, $linkGroupList);

        /** @var GeneralSidebarLinkList $linkList */
        $linkList = $linkGroupList[0];
        $this->assertEquals('Related', $linkList->getTitle());
        $links = $linkList->getLinks();
        $link = $links[0];

        $this->assertEquals('View MOT history', $link->getText());
    }

    public function testSidebarWithMaskButton()
    {
        $urlHelperPlugin = XMock::of(Url::class);
        $urlHelperPlugin->expects($this->at(0))->method('__invoke')->willReturnCallback(function ($route, $params) {
            $this->assertEquals($params['id'], self::VEHICLE_ID);
            $this->assertEquals('vehicle/detail/mask', $route);
        });
        $urlHelperPlugin->expects($this->at(1))->method('__invoke')->willReturnCallback(function ($route, $params, $params2) {
            $this->assertEquals($params['id'], self::VEHICLE_ID);
            $this->assertEquals('vehicle/detail/history', $route);
            $this->assertContains(VehicleController::BACK_TO_DETAIL, $params2['query']);
        });

        $searchParameters = new Parameters([VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_DETAIL]);

        $authorisationService = $this
            ->getMockBuilder(MotAuthorisationServiceInterface::class)
            ->getMock();
        $authorisationService
            ->expects($this->any())
            ->method('isGranted')
            ->with(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES)
            ->willReturn(true);

        $sidebar = new VehicleSidebar($urlHelperPlugin, $searchParameters, self::VEHICLE_ID, false, $authorisationService);

        $linkGroupList = $sidebar->getSidebarItems();

        $this->assertCount(2, $linkGroupList);

        /** @var GeneralSidebarLinkList $linkList */
        $linkList = $linkGroupList[0];
        $this->assertEquals('Enforcement', $linkList->getTitle());
        $links = $linkList->getLinks();
        $link = $links[0];

        $this->assertEquals('Mask this vehicle', $link->getText());
    }
}
