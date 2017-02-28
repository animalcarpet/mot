<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\Dashboard;
use Dashboard\Security\DashboardGuard;
use Zend\Mvc\Controller\Plugin\Url;

class DashboardViewModelBuilder
{
    /** @var Dashboard $dashboard */
    private $dashboard;

    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var Url $url */
    private $url;

    /** @var SlotsViewModel $slotsViewModel */
    private $slotsViewModel;

    /**
     * DashboardViewModelBuilder constructor.
     *
     * @param Dashboard $dashboard
     * @param DashboardGuard $dashboardGuard
     * @param Url $url
     */
    public function __construct(
        Dashboard $dashboard,
        DashboardGuard $dashboardGuard,
        Url $url

    ) {
        $this->dashboard = $dashboard;
        $this->dashboardGuard = $dashboardGuard;
        $this->url = $url;
    }

    /**
     * @return DashboardViewModel
     */
    public function build()
    {
        $dashboardViewModel = new DashboardViewModel(
            $this->buildHeroActionViewModel(),
            $this->buildNotificationsViewModel(),
            $this->buildDemoTestViewModel(),
            $this->buildAuthorisedExaminersViewModel(),
            $this->buildSpecialNoticesViewModel()
        );

        $dashboardViewModel->setShowDemoMessage($this->shouldShowDemoMessage());

        return $dashboardViewModel;
    }

    /**
     * @return HeroActionViewModel
     */
    private function buildHeroActionViewModel()
    {
        $this->slotsViewModel = $this->buildSlotsViewModel();

        $heroActionViewModel = new HeroActionViewModel(
            $this->dashboard->getHero(),
            $this->slotsViewModel,
            $this->dashboardGuard
        );

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
     * @return bool
     */
    public function shouldShowDemoMessage()
    {
        return $this->dashboardGuard->isDemoTestNeeded();
    }

    /**
     * @return AuthorisedExaminersViewModel
     */
    private function buildAuthorisedExaminersViewModel()
    {
        return AuthorisedExaminersViewModel::fromAuthorisedExaminers($this->dashboardGuard,
                                                                      $this->dashboard->getAuthorisedExaminers());
    }

    /**
     * @return SpecialNoticesViewModel
     */
    private function buildSpecialNoticesViewModel()
    {
        $specialNoticesViewModel = new SpecialNoticesViewModel(
            $this->dashboard->getSpecialNotice()->getUnreadCount(),
            $this->dashboard->getSpecialNotice()->getOverdueCount(),
            $this->dashboard->getSpecialNotice()->getDaysLeftToView(),
            $this->dashboardGuard
        );

        return $specialNoticesViewModel;
    }

    /**
     * @return SlotsViewModel
     */
    private function buildSlotsViewModel()
    {
        $slotsViewModel = new SlotsViewModel(
            $this->dashboard->getOverallSlotCount(),
            $this->dashboard->getOverallSiteCount()
        );

        return $slotsViewModel;
    }
}
