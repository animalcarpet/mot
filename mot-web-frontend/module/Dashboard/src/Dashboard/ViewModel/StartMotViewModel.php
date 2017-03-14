<?php

namespace Dashboard\ViewModel;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;

class StartMotViewModel
{
    /** @var bool $isTestingEnabled */
    private $isTestingEnabled;

    /** @var bool $isTesterAtAnySite */
    private $isTesterAtAnySite;

    /** @var bool $hasTestInProgress */
    private $hasTestInProgress;

    /** @var bool $testNumberInProgress */
    private $testNumberInProgress;

    /** @var string $enterResultsLabel */
    private $enterResultsLabel;

    /**
     * StartMotViewModel constructor.
     *
     * @param bool $isTestingEnabled
     * @param bool $isTesterAtAnySite
     * @param bool $hasTestInProgress
     * @param string $enterResultsLabel
     * @param int $testNumberInProgress
     * @param VehicleTestingStation $testerAtCurrentVts
     */
    public function __construct(
        $isTesterAtAnySite,
        $hasTestInProgress,
        $enterResultsLabel,
        $testNumberInProgress,
        $isTestingEnabled,
        $testerAtCurrentVts
    )
    {
        $this->isTesterAtAnySite = $isTesterAtAnySite;
        $this->hasTestInProgress = $hasTestInProgress;
        $this->enterResultsLabel = $enterResultsLabel;
        $this->testNumberInProgress = $testNumberInProgress;
        $this->isTestingEnabled = $isTestingEnabled;
        $this->testerAtCurrentVts = $testerAtCurrentVts;
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

    /**
     * @return string
     */
    public function getEnterResultsLabel()
    {
        return $this->enterResultsLabel;
    }

    /**
     * @return bool
     */
    public function canStartMotTest()
    {
        return $this->isTesterAtAnySite && $this->isTestingEnabled;
    }

    /**
     * @return bool
     */
    public function hasSlotsAvailable()
    {
        if (empty($this->testerAtCurrentVts)) {
            return true;
        }

        return $this->testerAtCurrentVts->getSlots() > 0;
    }
}
