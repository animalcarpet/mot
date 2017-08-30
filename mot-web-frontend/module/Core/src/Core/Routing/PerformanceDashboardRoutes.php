<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class PerformanceDashboardRoutes extends AbstractRoutes
{
    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     *
     * @return PerformanceDashboardRoutes
     */
    public static function of($object)
    {
        return new self($object);
    }

    public function performanceDashboard()
    {
        return $this->url(PerformanceDashboardRouteList::PERFORMANCE_DASHBOARD);
    }

    public function performanceDashboardTqi(array $queryParams = [])
    {
        return $this->url(PerformanceDashboardRouteList::PERFORMANCE_DASHBOARD_TQI, [], ['query' => $queryParams]);
    }

    public function performanceDashboardTqiComponentBreakdown(int $siteId, string $group, array $queryParams = [])
    {
        return $this->url(
            PerformanceDashboardRouteList::PERFORMANCE_DASHBOARD_TQI_BREAKDOWN_FOR_SITE,
            ['site' => $siteId, 'group' => $group],
            ['query' => $queryParams]
        );
    }
}