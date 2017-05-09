<?php

namespace PersonApiTest\Controller;

use PersonApi\Controller\UserStatsController;
use PersonApi\Service\UserStatsService;
use UserApi\Dashboard\Dto\DayStats;
use UserApi\Dashboard\Dto\MonthStats;

/**
 * Tests for UserStatsController.
 */
class UserStatsControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAction()
    {
        $dailyStatsMock = \DvsaCommonTest\TestUtils\XMock::of(DayStats::class);

        $dailyStatsMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([]));

        $monthStatsMock = \DvsaCommonTest\TestUtils\XMock::of(MonthStats::class);

        $monthStatsMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([]));

        $mock = \DvsaCommonTest\TestUtils\XMock::of(UserStatsService::class);

        $mock->expects($this->once())
            ->method('getUserDayStatsByPersonId')
            ->will($this->returnValue($dailyStatsMock));

        $mock->expects($this->once())
            ->method('getUserCurrentMonthStatsByPersonId')
            ->will($this->returnValue($monthStatsMock));

        $controller = new UserStatsController($mock);
        $json = $controller->get(1);

        $this->assertEquals(get_class($json), \Zend\View\Model\JsonModel::class);
    }
}
