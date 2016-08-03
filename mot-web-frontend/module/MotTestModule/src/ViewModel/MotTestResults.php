<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Utility\ArrayUtils;

/**
 * View model class to be used in the MOT Test Results view.
 */
class MotTestResults
{
    const BRAKE_TEST_OUTCOME_PASSED = 'Passed';
    const BRAKE_TEST_OUTCOME_FAILED = 'Failed';

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
    public function isBrakeTestResultPass()
    {
        $brakeResult = $this->motTestDto->getBrakeTestResult();

        return true === ArrayUtils::tryGet($brakeResult, 'generalPass');
    }

    /**
     * @return bool
     */
    public function isBrakeTestRecorded()
    {
        return !empty($this->motTestDto->getBrakeTestResult());
    }

    /**
     * @return string
     */
    public function getBrakeTestOutcome()
    {
        return true === $this->isBrakeTestResultPass()
            ? self::BRAKE_TEST_OUTCOME_PASSED : self::BRAKE_TEST_OUTCOME_FAILED;
    }
}
