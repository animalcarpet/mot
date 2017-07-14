<?php

namespace PersonApi\Controller;

use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\PerformanceDashboardStatsDto;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use PersonApi\Service\UserStatsService;

/**
 * Returns testing statistics.
 */
class UserStatsController extends AbstractDvsaRestfulController
{
    /**
     * @var UserStatsService
     */
    protected $userStatsService;

    public function __construct(UserStatsService $service)
    {
        $this->userStatsService = $service;
    }

    public function get($id)
    {
        $dayStats = $this->userStatsService->getUserDayStatsByPersonId($id);
        $monthStats = $this->userStatsService->getUserCurrentMonthStatsByPersonId($id);

        $stats = (new PerformanceDashboardStatsDto())
            ->setDayStats($dayStats)
            ->setMonthStats($monthStats);

        return $this->returnDto($stats);
    }
}
