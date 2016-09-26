<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use DvsaCommon\Dto\Common\MotTestDto;
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
     * @var MotTestDto
     */
    private $motTestDto;

    /**
     * MotTestResults constructor.
     *
     * @param MotTestDto $motTestDto
     */
    public function __construct(MotTestDto $motTestDto)
    {
        $this->motTestDto = $motTestDto;
    }

    /**
     * @return bool
     */
    public function isBrakeTestRecorded()
    {
        if (null === $this->motTestDto->getBrakeTestResult()) {
            return false;
        }

        $brakeTestResult = $this->motTestDto->getBrakeTestResult();

        return (!empty($brakeTestResult)
            && ArrayUtils::tryGet($brakeTestResult, 'generalPass') !== 'undefined')
            && ArrayUtils::tryGet($brakeTestResult, 'generalPass') !== null;
    }

    /**
     * @return bool
     */
    public function isOriginalBrakeTestRecorded()
    {
        if (null === $this->motTestDto->getMotTestOriginal()) {
            return false;
        }

        $brakeTestResult = $this->motTestDto->getMotTestOriginal()->getBrakeTestResult();

        return (!empty($brakeTestResult)
            && ArrayUtils::tryGet($brakeTestResult, 'generalPass') !== 'undefined')
            && ArrayUtils::tryGet($brakeTestResult, 'generalPass') !== null;
    }

    /**
     * @return bool
     */
    public function isBrakePerformanceNotTested()
    {
        return $this->motTestDto->getTesterBrakePerformanceNotTested();
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
        $brakeResult = $this->motTestDto->getBrakeTestResult();

        return true === (ArrayUtils::tryGet($brakeResult, 'generalPass')
            && ArrayUtils::tryGet($brakeResult, 'generalPass') !== "undefined");
    }

    /**
     * @return bool
     */
    private function isOriginalBrakeTestResultPass()
    {
        $brakeResult = $this->motTestDto->getMotTestOriginal()->getBrakeTestResult();

        return true === (ArrayUtils::tryGet($brakeResult, 'generalPass')
            && ArrayUtils::tryGet($brakeResult, 'generalPass') !== 'undefined');
    }

    /**
     * @return array
     */
    private function getBrakeTestResult()
    {
        $brakeTestResult = $this->motTestDto->getBrakeTestResult();

        return ($this->isRetest() && !$brakeTestResult) ?
            $this->motTestDto->getMotTestOriginal()->getBrakeTestResult() : $brakeTestResult;
    }

    /**
     * @return bool
     */
    private function isRetest()
    {
        return MotTestTypeCode::RE_TEST === $this->motTestDto->getTestType()->getCode();
    }
}
