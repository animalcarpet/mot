<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;

class HeroActionViewModel
{
    const AEDM_USER_ROLE = 'aedm';
    const TESTER = 'tester';
    const USER = 'user';

    /** @var bool $isRDCertificateLinkVisible */
    private $isRDCertificateLinkVisible = false;

    /** @var bool $isSlotCountVisible */
    private $isSlotCountVisible = false;

    /** @var bool $isSiteCountVisible */
    private $isSiteCountVisible = false;

    /** @var bool $isMotFormsLinkVisible */
    private $isMotFormsLinkVisible = false;

    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var string $userRole */
    private $userRole;

    /** @var SlotsViewModel $slotsViewModel */
    private $slotsViewModel;

    /**
     * HeroActionViewModel constructor.
     *
     * @param string         $userRole
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
        $roles = array(self::AEDM_USER_ROLE, self::TESTER, self::USER);

        // User with no roles assigned
        if ($this->userRole === self::USER && !$this->dashboardGuard->canPerformMotTest())
        {
            return false;
        }
        else if (in_array($this->userRole, $roles))
        {
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
     * @return bool
     */
    public function isMotFormsLinkVisible()
    {
        return $this->dashboardGuard->isTester();
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
            case self::TESTER;
                $this->isRDCertificateLinkVisible = true;
                break;
            case self::USER;
                $this->isRDCertificateLinkVisible = $this->dashboardGuard->canViewReplacementDuplicateCertificateLink();
                break;
            default:
        }
    }
}
