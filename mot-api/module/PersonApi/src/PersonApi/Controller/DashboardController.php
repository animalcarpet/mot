<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\Dashboard\Dto\DashboardData;
use PersonApi\Service\DashboardService;

/**
 * Returns all data for dashboard.
 */
class DashboardController extends AbstractDvsaRestfulController
{
    /**
     * @var DashboardService
     */
    protected $dashboardService;

    public function __construct(DashboardService $service)
    {
        $this->dashboardService = $service;
    }

    public function get($id)
    {
        /** @var $result DashboardData */
        $result = $this->dashboardService->getDataForDashboardByPersonId($id);

        return ApiResponse::jsonOk($result->toArray());
    }
}
