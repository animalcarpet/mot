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

    /** @var AuthorisedExaminersViewModel $authorisedExaminersViewModel */
    private $authorisedExaminersViewModel;

    /** @var SpecialNoticesViewModel $specialNoticesViewModel */
    private $specialNoticesViewModel;

    /** @var bool $showDemoMessage */
    private $showDemoMessage = false;

    /** @var bool $showYourPerformance */
    private $showYourPerformance = false;

    /** @var bool $showContingencyTests */
    private $showContingencyTests = false;

    /**
     * DashboardViewModel constructor.
     *
     * @param HeroActionViewModel          $heroActionViewModel
     * @param NotificationsViewModel       $notificationsViewModel
     * @param TrainingTestViewModel        $trainingTestViewModel
     * @param AuthorisedExaminersViewModel $authorisedExaminersViewModel
     * @param SpecialNoticesViewModel      $specialNoticesViewModel
     */
    public function __construct(
        HeroActionViewModel $heroActionViewModel,
        NotificationsViewModel $notificationsViewModel,
        TrainingTestViewModel $trainingTestViewModel,
        AuthorisedExaminersViewModel $authorisedExaminersViewModel,
        SpecialNoticesViewModel $specialNoticesViewModel
    ) {
        $this->heroActionViewModel = $heroActionViewModel;
        $this->notificationsViewModel = $notificationsViewModel;
        $this->trainingTestViewModel = $trainingTestViewModel;
        $this->authorisedExaminersViewModel = $authorisedExaminersViewModel;
        $this->specialNoticesViewModel = $specialNoticesViewModel;
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
}
