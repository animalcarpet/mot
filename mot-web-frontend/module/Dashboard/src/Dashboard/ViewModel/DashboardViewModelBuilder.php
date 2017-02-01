<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\Dashboard;
use Dashboard\Model\PersonalDetails;
use Dashboard\Security\DashboardGuard;
use Zend\Mvc\Controller\Plugin\Url;

class DashboardViewModelBuilder
{
    /**
     * @var Dashboard $dashboard
     */
    private $dashboard;

    /**
     * @var DashboardGuard $dashboardGuard
     */
    private $dashboardGuard;

    /**
     * @var PersonalDetails $personalDetails
     */
    private $personalDetails;

    /**
     * @var Url $url
     */
    private $url;

    /**
     * DashboardViewModelBuilder constructor.
     *
     * @param Dashboard       $dashboard
     * @param DashboardGuard  $dashboardGuard
     * @param PersonalDetails $personalDetails
     * @param Url             $url
     */
    public function __construct(
        Dashboard $dashboard,
        DashboardGuard $dashboardGuard,
        PersonalDetails $personalDetails,
        Url $url
    ) {
        $this->dashboard = $dashboard;
        $this->dashboardGuard = $dashboardGuard;
        $this->personalDetails = $personalDetails;
        $this->url = $url;
    }

    /**
     * @return HeroActionViewModel
     */
    private function buildHeroActionViewModel()
    {
        $heroActionViewModel = new HeroActionViewModel();

        return $heroActionViewModel;
    }

    /**
     * @return NotificationsViewModel
     */
    private function buildNotificationsViewModel()
    {
        return NotificationsViewModel::fromNotifications($this->dashboard->getNotifications(), $this->url);
    }

    /**
     * @return DemoTestViewModel
     */
    private function buildDemoTestViewModel()
    {
        $demoTestViewModel = new DemoTestViewModel($this->dashboardGuard);

        if ($this->dashboard->hasDemoTestInProgress()) {
            $demoTestViewModel->setInProgressTestNumber($this->dashboard->getInProgressDemoTestNumber());
        }

        return $demoTestViewModel;
    }

    /**
     * @return DashboardViewModel
     */
    public function build()
    {
        $dashboardViewModel = new DashboardViewModel(
            $this->buildHeroActionViewModel(),
            $this->buildNotificationsViewModel(),
            $this->buildDemoTestViewModel()
        );

        return $dashboardViewModel;
    }
}
