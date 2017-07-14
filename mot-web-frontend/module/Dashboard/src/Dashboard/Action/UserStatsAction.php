<?php

namespace Dashboard\Action;

use Core\Action\ViewActionResult;
use Dashboard\ViewModel\UserStatsViewModel;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\PerformanceDashboardStatsApiResource;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UserStatsAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Your performance dashboard';

    private $performanceDashboardStatsApiResource;

    public function __construct(
        PerformanceDashboardStatsApiResource $performanceDashboardStatsApiResource
    ) {
        $this->performanceDashboardStatsApiResource = $performanceDashboardStatsApiResource;
    }

    public function execute($userId) {
        $stats = $this->performanceDashboardStatsApiResource->getStats($userId);
        $breadcrumbs = $this->getBreadcrumbs();
        $viewModel = new UserStatsViewModel($stats);

        return $this->buildActionResult($viewModel, $breadcrumbs);
    }


    /**
     * @param UserStatsViewModel $vm
     * @param array $breadcrumbs
     * @return ViewActionResult
     */
    private function buildActionResult(
        UserStatsViewModel $vm,
        array $breadcrumbs
    )
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);

        return $actionResult;
    }

    private function getBreadcrumbs() {
        return [self::PAGE_TITLE => ''];
    }
}