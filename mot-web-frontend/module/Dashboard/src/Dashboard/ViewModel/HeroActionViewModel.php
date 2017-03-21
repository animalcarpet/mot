<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;

class HeroActionViewModel
{
    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var ReplacementDuplicateCertificateViewModel $replacementDuplicateCertificateViewModel */
    private $replacementDuplicateCertificateViewModel;

    /** @var SlotsViewModel $slotsViewModel*/
    private $slotsViewModel;

    /** @var StartMotViewModel $startMotViewModel */
    private $startMotViewModel;

    /**
     * HeroActionViewModel constructor.
     *
     * @param DashboardGuard                           $dashboardGuard
     * @param SlotsViewModel                           $slotsViewModel
     * @param ReplacementDuplicateCertificateViewModel $replacementDuplicateCertificateViewModel
     * @param StartMotViewModel                        $startMotViewModel
     */
    public function __construct(
        DashboardGuard $dashboardGuard,
        SlotsViewModel $slotsViewModel,
        ReplacementDuplicateCertificateViewModel $replacementDuplicateCertificateViewModel,
        StartMotViewModel $startMotViewModel
    )
    {
        $this->dashboardGuard = $dashboardGuard;
        $this->slotsViewModel = $slotsViewModel;
        $this->replacementDuplicateCertificateViewModel = $replacementDuplicateCertificateViewModel;
        $this->startMotViewModel = $startMotViewModel;
    }

    /**
     * If user has permission to view at least one element on the hero action (black box), then display the hero action.
     *
     * @return bool
     */
    public function isHeroActionVisible()
    {
        if ($this->startMotViewModel->canStartMotTest()) {
            return true;
        }
        if ($this->slotsViewModel->canViewSlotBalance()) {
            return true;
        }
        if ($this->dashboardGuard->canViewAeInformationLink()) {
            return true;
        }
        if ($this->dashboardGuard->canViewSiteInformationLink()) {
            return true;
        }
        if ($this->dashboardGuard->canViewUserSearchLink()) {
            return true;
        }
        if ($this->replacementDuplicateCertificateViewModel->canViewReplacementDuplicateCertificateLink()) {
            return true;
        }
        if ($this->dashboardGuard->canViewMotFormsLink()) {
            return true;
        }
        if ($this->dashboardGuard->canViewDemoTestRequestsLink()) {
            return true;
        }

        return false;
    }

    /**
     * @return DashboardGuard
     */
    public function getDashboardGuard()
    {
        return $this->dashboardGuard;
    }

    /**
     * @return ReplacementDuplicateCertificateViewModel
     */
    public function getReplacementDuplicateCertificateViewModel()
    {
        return $this->replacementDuplicateCertificateViewModel;
    }

    /**
     * @return StartMotViewModel
     */
    public function getStartMotViewModel()
    {
        return $this->startMotViewModel;
    }

    /**
     * @return SlotsViewModel
     */
    public function getSlotsViewModel()
    {
        return $this->slotsViewModel;
    }
}
