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
     * @param DashboardGuard $dashboardGuard
     * @param SlotsViewModel $slotsViewModel
     * @param ReplacementDuplicateCertificateViewModel $replacementDuplicateCertificateViewModel
     * @param StartMotViewModel $startMotViewModel
     */
    public function __construct(
        DashboardGuard $dashboardGuard,
        SlotsViewModel $slotsViewModel,
        ReplacementDuplicateCertificateViewModel $replacementDuplicateCertificateViewModel,
        StartMotViewModel $startMotViewModel)
    {
        $this->dashboardGuard = $dashboardGuard;
        $this->slotsViewModel = $slotsViewModel;
        $this->replacementDuplicateCertificateViewModel = $replacementDuplicateCertificateViewModel;
        $this->startMotViewModel = $startMotViewModel;
    }

    /**
     * @return bool
     */
    public function isHeroActionVisible()
    {
        /*
         * If user has permission to view at least one element on the hero action (black box), then display the hero action
         */
        return $this->replacementDuplicateCertificateViewModel->canViewReplacementDuplicateCertificateLink() ||
            $this->slotsViewModel->canViewSlotBalance() ||
            $this->startMotViewModel->canStartMotTest() ||
            $this->isMotFormsLinkVisible();
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

    /**
     * @return bool
     */
    public function isMotFormsLinkVisible()
    {
        return $this->dashboardGuard->isTester();
    }
}

