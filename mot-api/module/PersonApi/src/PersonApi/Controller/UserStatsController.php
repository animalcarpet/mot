<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
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
        $dayStats = $this->userStatsService->getUserDayStatsByPersonId($id)->toArray();
        $monthStats = $this->userStatsService->getUserCurrentMonthStatsByPersonId($id)->toArray();

        $result = array_merge($dayStats, $monthStats);

        return ApiResponse::jsonOk($result);
    }
}
