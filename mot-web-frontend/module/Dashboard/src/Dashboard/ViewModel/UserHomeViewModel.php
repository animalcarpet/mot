<?php

namespace Dashboard\ViewModel;

use Zend\View\Model\ViewModel;

class UserHomeViewModel extends ViewModel
{
    /** @var DashboardViewModel $dashboardViewModel */
    private $dashboardViewModel;

    /**
     * UserHomeViewModel constructor.
     *
     * @param DashboardViewModel $dashboardViewModel
     */
    public function __construct(
        DashboardViewModel $dashboardViewModel
    ) {
        $this->dashboardViewModel = $dashboardViewModel;
        parent::__construct(['dashboard' => $dashboardViewModel]);
    }
}
