<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Controller\VehicleSearchController;
use Zend\Mvc\Controller\Plugin\Url;

class NonMotTestViewModel
{
    const START_NON_MOT_TEST_BUTTON_TEXT = 'Start a non-MOT test';
    const RESUME_NON_MOT_TEST_BUTTON_TEXT = 'Enter non-MOT test results';
    const ACTION_RESUME_NON_MOT = 'action-resume-non-mot';
    const ACTION_START_NON_MOT = 'action-start-non-mot';

    /**
     * @var DashboardGuard $dashboardGuard
     */
    private $dashboardGuard;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var int $inProgressNonMotTestNumber
     */
    private $inProgressNonMotTestNumber;

    /**
     * NonMotTestViewModel constructor.
     *
     * @param DashboardGuard $dashboardGuard
     * @param Url            $url
     */
    public function __construct(DashboardGuard $dashboardGuard, Url $url)
    {
        $this->dashboardGuard = $dashboardGuard;
        $this->url = $url;
    }

    /**
     * @param int $nonMotTestNumber
     */
    public function setInProgressNonMotTestNumber($nonMotTestNumber)
    {
        $this->inProgressNonMotTestNumber = $nonMotTestNumber;
    }

    /**
     * @return LinkViewModel
     */
    public function getLinkViewModel()
    {
        if ($this->hasInProgressNonMotTest()) {
            $nonMotTestText = self::RESUME_NON_MOT_TEST_BUTTON_TEXT;
            $nonMotTestUrl = $this->url->fromRoute(
                MotTestController::ROUTE_MOT_TEST,
                ['motTestNumber' => $this->inProgressNonMotTestNumber]
            );
        } else {
            $nonMotTestText = self::START_NON_MOT_TEST_BUTTON_TEXT;
            $nonMotTestUrl = $this->url->fromRoute(
                VehicleSearchController::ROUTE_VEHICLE_SEARCH_NON_MOT
            );
        }

        return new LinkViewModel($nonMotTestText, $nonMotTestUrl);
    }

    /**
     * @return string
     */
    public function getLinkId()
    {
        return $this->hasInProgressNonMotTest() ? self::ACTION_RESUME_NON_MOT : self::ACTION_START_NON_MOT;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->dashboardGuard->canPerformNonMotTest();
    }

    /**
     * @return bool
     */
    private function hasInProgressNonMotTest()
    {
        return $this->inProgressNonMotTestNumber !== null;
    }
}
