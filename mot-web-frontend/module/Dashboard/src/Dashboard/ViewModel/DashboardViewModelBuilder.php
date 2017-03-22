<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\Dashboard;
use Dashboard\Security\DashboardGuard;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Zend\Mvc\Controller\Plugin\Url;

class DashboardViewModelBuilder
{
    /** @var Dashboard $dashboard */
    private $dashboard;

    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var Url $url */
    private $url;

    /** @var MotFrontendIdentityInterface $identity */
    private $identity;

    /**
     * DashboardViewModelBuilder constructor.
     *
     * @param MotFrontendIdentityInterface $identity
     * @param Dashboard $dashboard
     * @param DashboardGuard $dashboardGuard
     * @param Url $url
     */
    public function __construct(
        MotFrontendIdentityInterface $identity,
        Dashboard $dashboard,
        DashboardGuard $dashboardGuard,
        Url $url
    ) {
        $this->identity = $identity;
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
            $this->buildTrainingTestViewModel(),
            $this->buildAuthorisedExaminersViewModel(),
            $this->buildSpecialNoticesViewModel(),
            $this->buildAuthorisedExaminerManagementViewModel()
        );

        $dashboardViewModel->setShowDemoMessage($this->shouldShowDemoMessage());
        $dashboardViewModel->setShowYourPerformance($this->shouldShowYourPerformance());
        $dashboardViewModel->setShowContingencyTests($this->shouldShowContingencyTests());
        $dashboardViewModel->setShowAuthorisedExaminerManagement($this->shouldShowAuthorisedExaminerManagement());

        return $dashboardViewModel;
    }

    /**
     * @return bool
     */
    public function shouldShowDemoMessage()
    {
        return $this->dashboardGuard->isDemoTestNeeded();
    }

    /**
     * @return bool
     */
    public function shouldShowYourPerformance()
    {
        return $this->dashboardGuard->canViewYourPerformance();
    }

    /**
     * @return bool
     */
    public function shouldShowContingencyTests()
    {
        return $this->dashboardGuard->isTestingEnabled();
    }

    /**
     * @return bool
     */
    public function shouldShowAuthorisedExaminerManagement()
    {
        return $this->dashboardGuard->isAreaOffice1();
    }

    /**
     * @return HeroActionViewModel
     */
    private function buildHeroActionViewModel()
    {
        $slotsViewModel = $this->buildSlotsViewModel();
        $replacementDuplicateCertificateViewModel = $this->buildReplacementDuplicateCertificateViewModel();
        $startMotViewModel = $this->buildStartMotViewModel();

        $heroActionViewModel = new HeroActionViewModel(
            $this->dashboardGuard,
            $slotsViewModel,
            $replacementDuplicateCertificateViewModel,
            $startMotViewModel
        );

        return $heroActionViewModel;
    }

    /**
     * @return NotificationsViewModel
     */
    private function buildNotificationsViewModel()
    {
        return NotificationsViewModel::fromNotifications(
            $this->dashboard->getNotifications(),
            $this->dashboard->getUnreadNotificationsCount(),
            $this->url
        );
    }

    /**
     * @return TrainingTestViewModel
     */
    private function buildTrainingTestViewModel()
    {
        $trainingTestViewModel = new TrainingTestViewModel($this->dashboardGuard);

        if ($this->dashboard->hasDemoTestInProgress()) {
            $trainingTestViewModel->setInProgressTestNumber($this->dashboard->getInProgressDemoTestNumber());
        }

        return $trainingTestViewModel;
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
            $this->dashboardGuard->canViewSlotBalance(),
            $this->dashboard->getOverallSlotCount(),
            $this->dashboard->getOverallSiteCount()
        );

        return $slotsViewModel;
    }

    /**
     * @return ReplacementDuplicateCertificateViewModel
     */
    private function buildReplacementDuplicateCertificateViewModel()
    {
        $replacementDuplicateCertificateViewModel = new ReplacementDuplicateCertificateViewModel(
            $this->dashboard->hasTestInProgress(),
            $this->dashboardGuard->canViewReplacementDuplicateCertificateLink()
        );

        return $replacementDuplicateCertificateViewModel;
    }

    /**
     * @return StartMotViewModel
     */
    private function buildStartMotViewModel()
    {
        $testersCurrentVts = $this->identity->getCurrentVts();

        $startMotViewModel = new StartMotViewModel(
            $this->dashboard->isTesterAtAnySite(),
            $this->dashboard->hasTestInProgress(),
            $this->dashboard->getEnterTestResultsLabel(),
            $this->dashboard->getInProgressTestNumber(),
            $this->dashboardGuard->isTestingEnabled(),
            $testersCurrentVts
        );

        return $startMotViewModel;
    }

    /**
     * @return AuthorisedExaminerManagementViewModel
     */
    private function buildAuthorisedExaminerManagementViewModel()
    {
        $authorisedExaminerManagementViewModel = new AuthorisedExaminerManagementViewModel(
            $this->dashboardGuard->canCreateAuthorisedExaminer(),
            $this->dashboardGuard->canCreateVehicleTestingStation()
        );

        return $authorisedExaminerManagementViewModel;
    }
}
