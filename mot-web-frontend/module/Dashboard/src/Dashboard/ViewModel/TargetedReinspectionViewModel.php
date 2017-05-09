<?php

namespace Dashboard\ViewModel;

use DvsaMotTest\Controller\MotTestController;
use Zend\Mvc\Controller\Plugin\Url;

class TargetedReinspectionViewModel
{
    /** @var Url $url */
    private $url;

    /** @var bool $isVehicleExaminer */
    private $isVehicleExaminer;

    /** @var bool $hasTestInProgress */
    private $hasTestInProgress;

    /** @var int $testNumberInProgress */
    private $testNumberInProgress;

    /**
     * StartMotViewModel constructor.
     *
     * @param Url  $url
     * @param bool $isVehicleExaminer
     * @param bool $hasTestInProgress
     * @param int  $testNumberInProgress
     */
    public function __construct(
        $url,
        $isVehicleExaminer,
        $hasTestInProgress,
        $testNumberInProgress
    ) {
        $this->url = $url;
        $this->isVehicleExaminer = $isVehicleExaminer;
        $this->hasTestInProgress = $hasTestInProgress;
        $this->testNumberInProgress = $testNumberInProgress;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url->fromRoute(
            MotTestController::ROUTE_MOT_TEST,
            ['motTestNumber' => $this->getTestNumberInProgress()]
        );
    }

    /**
     * @return bool
     */
    public function isVehicleExaminer()
    {
        return $this->isVehicleExaminer;
    }

    /**
     * @return bool
     */
    public function hasTestInProgress()
    {
        return $this->hasTestInProgress;
    }

    /**
     * @return int
     */
    public function getTestNumberInProgress()
    {
        return $this->testNumberInProgress;
    }
}
