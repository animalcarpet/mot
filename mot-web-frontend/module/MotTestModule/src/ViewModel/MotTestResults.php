<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResult;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;

/**
 * View model class to be used in the MOT Test Results view.
 */
class MotTestResults
{
    const BRAKE_TEST_OUTCOME_NOT_TESTED = 'Not tested';
    const BRAKE_TEST_OUTCOME_NOT_RECORDED = 'Not recorded';
    const BRAKE_TEST_OUTCOME_PASSED = 'Passed';
    const BRAKE_TEST_OUTCOME_FAILED = 'Failed';
    const INITIAL_BRAKE_TEST_OUTCOME_FAILED = 'Initial test failed';
    const INITIAL_BRAKE_TEST_OUTCOME_PASSED = 'Initial test passed';

    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var MotTest
     */
    private $originalMotTest;

    /**
     * MotTestResults constructor.
     *
     * @param MotTest $motTest
     * @param MotTest $originalMotTest
     */
    public function __construct(MotTest $motTest, MotTest $originalMotTest = null)
    {
        $this->motTest = $motTest;
        $this->originalMotTest = $originalMotTest;
    }

    /**
     * @return bool
     */
    public function shouldDisableSubmitButton()
    {
        $submissionStatus = !is_null($this->motTest->getPendingDetails()->getCurrentSubmissionStatus())
            ? $this->motTest->getPendingDetails()->getCurrentSubmissionStatus() : null;

        return $submissionStatus == 'INCOMPLETE';
    }

    /**
     * @return bool
     */
    public function isBrakeTestRecorded()
    {
        if (null === $this->motTest->getBrakeTestResult()) {
            return false;
        }

        $brakeTestResult = $this->motTest->getBrakeTestResult();

        return (!empty($brakeTestResult)
            && $brakeTestResult->generalPass !== 'undefined')
            && $brakeTestResult->generalPass !== null;
    }

    /**
     * @return bool
     */
    public function isOriginalBrakeTestRecorded()
    {
        if (null === $this->originalMotTest || null === $this->originalMotTest->getMotTestNumber()) {
            return false;
        }

        $brakeTestResult = $this->originalMotTest->getBrakeTestResult();

        return (!empty($brakeTestResult)
            && $brakeTestResult->generalPass !== 'undefined')
            && $brakeTestResult->generalPass !== null;
    }

    /**
     * @return bool
     */
    public function isBrakePerformanceNotTested()
    {
        return $this->motTest->isTesterBrakePerformanceNotTested();
    }

    /**
     * @return string
     */
    public function getBrakeTestOutcome()
    {
        if ($this->isBrakePerformanceNotTested()) {
            return self::BRAKE_TEST_OUTCOME_NOT_TESTED;
        }

        if (!$this->hasBrakeTestResult()) {
            return self::BRAKE_TEST_OUTCOME_NOT_RECORDED;
        }

        if ($this->isBrakeTestRecorded()) {
            return $this->isBrakeTestResultPass() ? self::BRAKE_TEST_OUTCOME_PASSED : self::BRAKE_TEST_OUTCOME_FAILED;
        }

        if ($this->isOriginalBrakeTestRecorded()) {
            return $this->isOriginalBrakeTestResultPass() ?
                self::INITIAL_BRAKE_TEST_OUTCOME_PASSED : self::INITIAL_BRAKE_TEST_OUTCOME_FAILED;
        }

        return '';
    }

    /**
     * @return bool
     */
    public function hasBrakeTestResult()
    {
        return (bool) $this->getBrakeTestResult();
    }

    /**
     * @return bool
     */
    private function isBrakeTestResultPass()
    {
        $brakeResult = $this->motTest->getBrakeTestResult();

        return true === $brakeResult->generalPass
            && $brakeResult->generalPass !== "undefined";
    }

    /**
     * @return bool
     */
    private function isOriginalBrakeTestResultPass()
    {
        $brakeResult = $this->originalMotTest->getBrakeTestResult();

        return true === $brakeResult->generalPass
            && $brakeResult->generalPass !== 'undefined';
    }

    /**
     * @return BrakeTestResult
     */
    private function getBrakeTestResult()
    {
        $brakeTestResult = $this->motTest->getBrakeTestResult();

        return ($this->isRetest() && !$brakeTestResult) ?
            $this->originalMotTest->getBrakeTestResult() : $brakeTestResult;
    }

    /**
     * @return bool
     */
    private function isRetest()
    {
        return MotTestTypeCode::RE_TEST === $this->motTest->getTestTypeCode();
    }
}
