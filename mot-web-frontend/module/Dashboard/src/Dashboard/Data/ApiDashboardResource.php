<?php

namespace Dashboard\Data;

use Application\Data\ApiResources;
use DvsaCommon\UrlBuilder\DashboardUrlBuilder;

/**
 * Handles calls to API, paths:
 *      /person/:personId/dashboard.
 */
class ApiDashboardResource extends ApiResources
{
    /** @var $dashboardData array */
    private static $dashboardData = null;

    /**
     * Gets all data for dashboard.
     *
     * @param int $personId
     *
     * @return array
     */
    public function get($personId)
    {
        if (null === self::$dashboardData) {
            $path = DashboardUrlBuilder::dashboard($personId)->toString();
            self::$dashboardData = $this->restGet($path)['data'];
        }

        return self::$dashboardData;
    }
}
