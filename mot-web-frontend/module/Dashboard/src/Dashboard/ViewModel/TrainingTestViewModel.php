<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;

class TrainingTestViewModel
{
    /**
     * @var DashboardGuard $dashboardGuard
     */
    private $dashboardGuard;

    /**
     * @var int $inProgressTestNumber
     */
    private $inProgressTestNumber;

    /**
     * TrainingTestViewModel constructor.
     *
     * @param DashboardGuard $dashboardGuard
     */
    public function __construct(DashboardGuard $dashboardGuard)
    {
        $this->dashboardGuard = $dashboardGuard;
    }

    /**
     * @param int $testNumber
     */
    public function setInProgressTestNumber($testNumber)
    {
        $this->inProgressTestNumber = $testNumber;
    }

    /**
     * @return LinkViewModel
     */
    public function getLinkViewModel()
    {
        $linkText = $this->hasInProgressTest() ? 'Resume training test' : 'Start training test';
        $linkHref = $this->hasInProgressTest() ?
            MotTestUrlBuilder::motTest($this->inProgressTestNumber)->toString() :
            VehicleUrlBuilder::trainingSearch()->toString();

        return new LinkViewModel($linkText, $linkHref);
    }

    /**
     * @return LinkViewModel
     */
    public function getLink()
    {
        return $this->getLinkViewModel();
    }

    /**
     * @return string
     */
    public function getLinkId()
    {
        return $this->hasInProgressTest() ? 'action-resume-mot-demonstration' : 'action-start-mot-demonstration';
    }

    /**
     * @return bool
     */
    private function hasInProgressTest()
    {
        return $this->inProgressTestNumber !== null;
    }
}
