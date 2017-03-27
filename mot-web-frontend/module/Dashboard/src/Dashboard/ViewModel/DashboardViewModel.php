<?php

namespace Dashboard\ViewModel;

class DashboardViewModel
{
    /** @var HeroActionViewModel $heroActionViewModel */
    private $heroActionViewModel;

    /** @var NotificationsViewModel $notificationsViewModel */
    private $notificationsViewModel;

    /** @var TrainingTestViewModel $trainingTestViewModel */
    private $trainingTestViewModel;

    /** @var NonMotTestViewModel $nonMotTestViewModel */
    private $nonMotTestViewModel;

    /** @var AuthorisedExaminersViewModel $authorisedExaminersViewModel */
    private $authorisedExaminersViewModel;

    /** @var SpecialNoticesViewModel $specialNoticesViewModel */
    private $specialNoticesViewModel;

    /** @var AuthorisedExaminerManagementViewModel $authorisedExaminerManagementViewModel */
    private $authorisedExaminerManagementViewModel;

    /** @var bool $showDemoMessage */
    private $showDemoMessage = false;

    /** @var bool $showAuthorisedExaminerManagement */
    private $showAuthorisedExaminerManagement = false;

    /** @var bool $showYourPerformance */
    private $showYourPerformance = false;

    /** @var bool $showContingencyTests */
    private $showContingencyTests = false;

    /** @var bool $showFinancialReports */
    private $showFinancialReports = false;

    /**
     * DashboardViewModel constructor.
     *
     * @param HeroActionViewModel          $heroActionViewModel
     * @param NotificationsViewModel       $notificationsViewModel
     * @param TrainingTestViewModel        $trainingTestViewModel
     * @param NonMotTestViewModel          $nonMotTestViewModel
     * @param AuthorisedExaminersViewModel $authorisedExaminersViewModel
     * @param SpecialNoticesViewModel $specialNoticesViewModel
     * @param AuthorisedExaminerManagementViewModel $authorisedExaminerManagementViewModel
     */
    public function __construct(
        HeroActionViewModel $heroActionViewModel,
        NotificationsViewModel $notificationsViewModel,
        TrainingTestViewModel $trainingTestViewModel,
        NonMotTestViewModel $nonMotTestViewModel,
        AuthorisedExaminersViewModel $authorisedExaminersViewModel,
        SpecialNoticesViewModel $specialNoticesViewModel,
        AuthorisedExaminerManagementViewModel $authorisedExaminerManagementViewModel
    ) {
        $this->heroActionViewModel = $heroActionViewModel;
        $this->notificationsViewModel = $notificationsViewModel;
        $this->trainingTestViewModel = $trainingTestViewModel;
        $this->nonMotTestViewModel = $nonMotTestViewModel;
        $this->authorisedExaminersViewModel = $authorisedExaminersViewModel;
        $this->specialNoticesViewModel = $specialNoticesViewModel;
        $this->authorisedExaminerManagementViewModel = $authorisedExaminerManagementViewModel;
    }

    /**
     * @return HeroActionViewModel
     */
    public function getHeroActionViewModel()
    {
        return $this->heroActionViewModel;
    }

    /**
     * @return TrainingTestViewModel
     */
    public function getTrainingTestViewModel()
    {
        return $this->trainingTestViewModel;
    }

    /**
     * @return NonMotTestViewModel
     */
    public function getNonMotTestViewModel()
    {
        return $this->nonMotTestViewModel;
    }

    /**
     * @return NotificationsViewModel
     */
    public function getNotificationsViewModel()
    {
        return $this->notificationsViewModel;
    }

    /**
     * @return AuthorisedExaminersViewModel
     */
    public function getAuthorisedExaminersViewModel()
    {
        return $this->authorisedExaminersViewModel;
    }

    /**
     * @return SpecialNoticesViewModel
     */
    public function getSpecialNoticeViewModel()
    {
        return $this->specialNoticesViewModel;
    }

    /**
     * @return bool
     */
    public function getShowDemoMessage()
    {
        return $this->showDemoMessage;
    }

    /**
     * @param bool $showDemoMessage
     */
    public function setShowDemoMessage($showDemoMessage)
    {
        $this->showDemoMessage = $showDemoMessage;
    }

    /**
     * @return bool
     */
    public function getShowYourPerformance()
    {
        return $this->showYourPerformance;
    }

    /**
     * @param bool $showYourPerformance
     */
    public function setShowYourPerformance($showYourPerformance)
    {
        $this->showYourPerformance = $showYourPerformance;
    }

    /**
     * @return bool
     */
    public function getShowContingencyTests()
    {
        return $this->showContingencyTests;
    }

    /**
     * @param bool $showContingencyTests
     */
    public function setShowContingencyTests($showContingencyTests)
    {
        $this->showContingencyTests = $showContingencyTests;
    }

    /**
     * @return bool
     */
    public function getShowFinancialReports()
    {
        return $this->showFinancialReports;
    }

    /**
     * @param bool $showFinancialReports
     */
    public function setShowFinancialReports($showFinancialReports)
    {
        $this->showFinancialReports = $showFinancialReports;
    }

    /**
     * @return bool
     */
    public function getShowAuthorisedExaminerManagement()
    {
        return $this->showAuthorisedExaminerManagement;
    }

    /**
     * @param bool $showAuthorisedExaminerManagement
     */
    public function setShowAuthorisedExaminerManagement($showAuthorisedExaminerManagement)
    {
        $this->showAuthorisedExaminerManagement =  $showAuthorisedExaminerManagement;
    }

    /**
     * @return AuthorisedExaminerManagementViewModel
     */
    public function getAuthorisedExaminerManagementViewModel()
    {
        return $this->authorisedExaminerManagementViewModel;
    }
}
