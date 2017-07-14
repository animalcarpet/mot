<?php

namespace DashboardTest\Action;

use Dashboard\Action\UserStatsAction;
use Dashboard\ViewModel\UserStatsViewModel;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\DayPerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\MonthPerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\PerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\PerformanceDashboardStatsApiResource;
use DvsaCommon\Date\TimeSpan;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class UserStatsActionTest extends PHPUnit_Framework_TestCase
{
    const USER_ID = 1;

    private $breadcrumbs = [
        UserStatsAction::PAGE_TITLE => '',
    ];

    /**
     * @dataProvider performanceDashboardStatsDataProvider
     * @param PerformanceDashboardStatsDto $statsDto
     */
    public function testLayoutForDto(PerformanceDashboardStatsDto $statsDto)
    {
        $action = $this->getUserStatsActionWithPerformanceDashboardStatsDto($statsDto);
        $result = $action->execute(self::USER_ID);

        /** @var UserStatsViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($result->layout()->getTemplate());
        $this->assertNotNull($vm);
        $this->assertSame($statsDto->getDayStats(), $vm->getDayStats());
        $this->assertSame($statsDto->getMonthStats(), $vm->getMonthStats());
        $this->assertSame(UserStatsAction::PAGE_TITLE, $result->layout()->getPageTitle());
        $this->assertSame($this->breadcrumbs, $result->layout()->getBreadcrumbs());
    }

    private function getUserStatsActionWithPerformanceDashboardStatsDto(PerformanceDashboardStatsDto $statsDto)
    {
        /** @var PerformanceDashboardStatsApiResource|MockObj $performanceDashboardStatsApiResourceMock */
        $performanceDashboardStatsApiResourceMock =
            XMock::of(PerformanceDashboardStatsApiResource::class);

        $performanceDashboardStatsApiResourceMock
            ->method('getStats')
            ->willReturn($statsDto);

        return new UserStatsAction($performanceDashboardStatsApiResourceMock);
    }

    private function buildPerformanceDashboardStatsDto()
    {
        $dayStats = (new DayPerformanceDashboardStatsDto())
            ->setNumberOfPasses(10)
            ->setNumberOfFails(7)
            ->setTotal(17);

        $monthStats = (new MonthPerformanceDashboardStatsDto())
            ->setPassedTestsCount(4)
            ->setFailedTestsCount(2)
            ->setTotalTestsCount(6)
            ->setFailRate(0.5)
            ->setAverageTime(new TimeSpan(0,0,0,15));

        $performanceDashboardStatsDto = (new PerformanceDashboardStatsDto())
            ->setMonthStats($monthStats)
            ->setDayStats($dayStats);

        return $performanceDashboardStatsDto;
    }

    private function buildEmptyPerformanceDashboardStatsDto()
    {
       $performanceDashboardStatsDto =
           (new PerformanceDashboardStatsDto())
                ->setDayStats(new DayPerformanceDashboardStatsDto())
                ->setMonthStats(new MonthPerformanceDashboardStatsDto());

       return $performanceDashboardStatsDto;
    }

    /**
     * @return array
     */
    public function performanceDashboardStatsDataProvider()
    {
        return [
            [
                $this->buildEmptyPerformanceDashboardStatsDto()
            ],
            [
                $this->buildPerformanceDashboardStatsDto()
            ],
        ];
    }
}
