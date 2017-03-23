<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;

class HeroActionViewModel
{
    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var SlotsViewModel $slotsViewModel*/
    private $slotsViewModel;

    /** @var StartMotViewModel $startMotViewModel */
    private $startMotViewModel;

    /** @var TargetedReinspectionViewModel $targetedReinspectionViewModel */
    private $targetedReinspectionViewModel;

    /**
     * HeroActionViewModel constructor.
     *
     * @param DashboardGuard                           $dashboardGuard
     * @param SlotsViewModel                           $slotsViewModel
     * @param StartMotViewModel                        $startMotViewModel
     * @param TargetedReinspectionViewModel            $targetedReinspectionViewModel
     */
    public function __construct(
        DashboardGuard $dashboardGuard,
        SlotsViewModel $slotsViewModel,
        StartMotViewModel $startMotViewModel,
        TargetedReinspectionViewModel $targetedReinspectionViewModel)
    {
        $this->dashboardGuard = $dashboardGuard;
        $this->slotsViewModel = $slotsViewModel;
        $this->startMotViewModel = $startMotViewModel;
        $this->targetedReinspectionViewModel = $targetedReinspectionViewModel;
    }

    /**
     * If user has permission to view at least one element on the hero action (black box), then display the hero action.
     *
     * @return bool
     */
    public function isHeroActionVisible()
    {
        if ($this->dashboardGuard->isTester()) {
            return true;
        }
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
        if ($this->dashboardGuard->canViewReplacementDuplicateCertificateLink()) {
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
     * @return TargetedReinspectionViewModel
     */
    public function getTargetedReinspectionViewModel()
    {
        return $this->targetedReinspectionViewModel;
    }
}
