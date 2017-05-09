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
     * @param Dashboard                    $dashboard
     * @param DashboardGuard               $dashboardGuard
     * @param Url                          $url
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
            $this->buildNonMotTestViewModel(),
            $this->buildAuthorisedExaminersViewModel(),
            $this->buildSpecialNoticesViewModel(),
            $this->buildAuthorisedExaminerManagementViewModel()
        );

        $dashboardViewModel->setShowDemoMessage($this->shouldShowDemoMessage());
        $dashboardViewModel->setShowYourPerformance($this->shouldShowYourPerformance());
        $dashboardViewModel->setShowContingencyTests($this->shouldShowContingencyTests());
        $dashboardViewModel->setShowAuthorisedExaminerManagement($this->shouldShowAuthorisedExaminerManagement());
        $dashboardViewModel->setShowFinancialReports($this->shouldShowFinancialReports());

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
        return $this->canStartMotTest();
    }

    /**
     * @return bool
     */
    public function shouldShowAuthorisedExaminerManagement()
    {
        return $this->dashboardGuard->isAreaOffice1();
    }

    /**
     * @return bool
     */
    public function shouldShowFinancialReports()
    {
        return $this->dashboardGuard->canGenerateFinancialReports();
    }

    /**
     * @return bool
     */
    private function canStartMotTest()
    {
        return $this->dashboardGuard->isTestingEnabled() && $this->dashboard->isTesterAtAnySite();
    }

    /**
     * @return HeroActionViewModel
     */
    private function buildHeroActionViewModel()
    {
        $slotsViewModel = $this->buildSlotsViewModel();
        $startMotViewModel = $this->buildStartMotViewModel();
        $targetedReinspectionViewModel = $this->buildTargetedReinspectionViewModel();
        $testingAdviceViewModel = $this->buildTestingAdviceViewModel();

        $heroActionViewModel = new HeroActionViewModel(
            $this->dashboardGuard,
            $slotsViewModel,
            $startMotViewModel,
            $targetedReinspectionViewModel,
            $testingAdviceViewModel
        );

        return $heroActionViewModel;
    }

    private function buildTestingAdviceViewModel()
    {
        $testingAdviceViewModel = new TestingAdviceViewModel(
            $this->dashboard->getIsTechnicalAdvicePresent(),
            $this->dashboard->getTestedVehicleId(),
            $this->dashboard->getInProgressTestNumber()
        );

        return $testingAdviceViewModel;
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
        $trainingTestViewModel = new TrainingTestViewModel($this->url);

        if ($this->dashboard->hasDemoTestInProgress()) {
            $trainingTestViewModel->setInProgressTestNumber($this->dashboard->getInProgressDemoTestNumber());
        }

        return $trainingTestViewModel;
    }

    /**
     * @return NonMotTestViewModel
     */
    private function buildNonMotTestViewModel()
    {
        $nonMotTestViewModel = new NonMotTestViewModel($this->dashboardGuard, $this->url);

        if ($this->dashboard->hasNonMotTestInProgress()) {
            $nonMotTestViewModel->setInProgressNonMotTestNumber($this->dashboard->getInProgressNonMotTestNumber());
        }

        return $nonMotTestViewModel;
    }

    /**
     * @return AuthorisedExaminersViewModel
     */
    private function buildAuthorisedExaminersViewModel()
    {
        return AuthorisedExaminersViewModel::fromAuthorisedExaminers(
            $this->dashboardGuard,
            $this->dashboard->getAuthorisedExaminers(),
            $this->url
        );
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
     * @return StartMotViewModel
     */
    private function buildStartMotViewModel()
    {
        $testersCurrentVts = $this->identity->getCurrentVts();

        $startMotViewModel = new StartMotViewModel(
            $this->url,
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

    /**
     * @return TargetedReinspectionViewModel
     */
    private function buildTargetedReinspectionViewModel()
    {
        $targetedReinspectionViewModel = new TargetedReinspectionViewModel(
            $this->url,
            $this->dashboardGuard->isVehicleExaminer(),
            $this->dashboard->hasTestInProgress(),
            $this->dashboard->getInProgressTestNumber()
        );

        return $targetedReinspectionViewModel;
    }
}
