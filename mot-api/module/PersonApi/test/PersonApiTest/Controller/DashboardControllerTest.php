<?php

namespace PersonApi\Controller;

use UserApi\Dashboard\Dto\DashboardData;
use PersonApi\Service\DashboardService;

/**
 * Class DashboardControllerTest.
 */
class DashboardControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAction()
    {
        $dashboardDataMock = \DvsaCommonTest\TestUtils\XMock::of(DashboardData::class);

        $dashboardDataMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([]));

        $mock = \DvsaCommonTest\TestUtils\XMock::of(DashboardService::class);

        $mock->expects($this->once())
            ->method('getDataForDashboardByPersonId')
            ->will($this->returnValue($dashboardDataMock));

        $controller = new DashboardController($mock);
        $json = $controller->get(1);

        $this->assertEquals(get_class($json), \Zend\View\Model\JsonModel::class);
    }
}
