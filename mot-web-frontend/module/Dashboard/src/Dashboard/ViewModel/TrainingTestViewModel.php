<?php

namespace Dashboard\ViewModel;

use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Controller\VehicleSearchController;
use Zend\Mvc\Controller\Plugin\Url;

class TrainingTestViewModel
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var int $inProgressTestNumber
     */
    private $inProgressTestNumber;

    /**
     * TrainingTestViewModel constructor.
     *
     * @param Url            $url
     */
    public function __construct(Url $url)
    {
        $this->url = $url;
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
        if ($this->hasInProgressTest()) {
            $linkText = 'Resume training test';
            $linkHref = $this->url->fromRoute(
                MotTestController::ROUTE_MOT_TEST,
                ['motTestNumber' => $this->inProgressTestNumber]
            );
        } else {
            $linkText = 'Start training test';
            $linkHref = $this->url->fromRoute(
                VehicleSearchController::ROUTE_VEHICLE_SEARCH_TRAINING
            );
        }

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
