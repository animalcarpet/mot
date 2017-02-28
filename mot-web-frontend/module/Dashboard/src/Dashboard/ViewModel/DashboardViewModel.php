<?php

namespace Dashboard\ViewModel;

class DashboardViewModel
{
    /** @var HeroActionViewModel $heroActionViewModel */
    private $heroActionViewModel;

    /** @var NotificationsViewModel $notificationsViewModel */
    private $notificationsViewModel;

    /** @var DemoTestViewModel $demoTestViewModel */
    private $demoTestViewModel;

    /** @var AuthorisedExaminersViewModel $authorisedExaminersViewModel */
    private $authorisedExaminersViewModel;

    /** @var SpecialNoticesViewModel $specialNoticesViewModel */
    private $specialNoticesViewModel;

    /** @var bool $showDemoMessage */
    private $showDemoMessage = false;

    /**
     * DashboardViewModel constructor.
     *
     * @param HeroActionViewModel $heroActionViewModel
     * @param NotificationsViewModel $notificationsViewModel
     * @param DemoTestViewModel $demoTestViewModel
     * @param AuthorisedExaminersViewModel $authorisedExaminersViewModel
     * @param SpecialNoticesViewModel $specialNoticesViewModel
     */
    public function __construct(
        HeroActionViewModel $heroActionViewModel,
        NotificationsViewModel $notificationsViewModel,
        DemoTestViewModel $demoTestViewModel,
        AuthorisedExaminersViewModel $authorisedExaminersViewModel,
        SpecialNoticesViewModel $specialNoticesViewModel
    ) {
        $this->heroActionViewModel = $heroActionViewModel;
        $this->notificationsViewModel = $notificationsViewModel;
        $this->demoTestViewModel = $demoTestViewModel;
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
     * @return DemoTestViewModel
     */
    public function getDemoTestViewModel()
    {
        return $this->demoTestViewModel;
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
}
