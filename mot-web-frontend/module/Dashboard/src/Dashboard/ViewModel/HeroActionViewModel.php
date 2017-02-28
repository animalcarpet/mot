<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;

class HeroActionViewModel
{
    const AEDM_USER_ROLE = 'aedm';

    /** @var bool */
    private $isRDCertificateLinkVisible = false;

    /** @var bool */
    private $isSlotCountVisible = false;

    /** @var bool */
    private $isSiteCountVisible = false;

    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var string */
    private $userRole;

    /** @var SlotsViewModel */
    private $slotsViewModel;

    /**
     * HeroActionViewModel constructor.
     *
     * @param $userRole
     * @param SlotsViewModel $slotsViewModel
     * @param DashboardGuard $dashboardGuard
     */
    public function __construct(
        $userRole,
        SlotsViewModel $slotsViewModel,
        DashboardGuard $dashboardGuard
    )
    {
        $this->userRole = $userRole;
        $this->slotsViewModel = $slotsViewModel;
        $this->dashboardGuard = $dashboardGuard;
    }

    /**
     * @return bool
     */
    public function isHeroActionVisible()
    {
        // Other roles can be added here
        $roles = array(self::AEDM_USER_ROLE);

        if (in_array($this->userRole, $roles)) {
            $this->buildHeroAction();
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSlotCountVisible()
    {
        return $this->isSlotCountVisible;
    }

    /**
     * @return bool
     */
    public function isSiteCountVisible()
    {
        return $this->isSiteCountVisible;
    }

    /**
     * @return bool
     */
    public function isOverallSiteCountVisible()
    {
        return $this->slotsViewModel->isOverallSiteCountVisible();
    }

    /**
     * @return bool
     */
    public function isReplacementDuplicateCertificateLinkVisible()
    {
        return $this->isRDCertificateLinkVisible;
    }

    /**
     * @return int
     */
    public function getOverallSlotCount()
    {
        return $this->slotsViewModel->getOverallSlotCount();
    }

    /**
     * @return int
     */
    public function getOverallSiteCount()
    {
        return $this->slotsViewModel->getOverallSiteCount();
    }

    /**
     * Builds the elements to be displayed in the Hero Action box based on the user's role
     */
    private function buildHeroAction()
    {
        switch($this->userRole) {
            case self::AEDM_USER_ROLE;
                $this->isRDCertificateLinkVisible = true;
                $this->isSlotCountVisible = true;
                $this->isSiteCountVisible = true;
                break;
            default:
        }
    }
}
