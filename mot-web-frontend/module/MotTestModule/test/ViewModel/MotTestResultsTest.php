<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\MotTestResults;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestTypeCode;
use PHPUnit_Framework_TestCase;

class MotTestResultsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function isBrakeTestRecordedDataProvider()
    {
        return [
            [null, false],
            [[], false],
            [['generalPass' => null], false],
            [['generalPass' => 'undefined'], false],
            [['generalPass' => false], true],
            [['generalPass' => true], true],
        ];
    }

    /**
     * @dataProvider isBrakeTestRecordedDataProvider
     *
     * @param mixed $brakeTestResult
     * @param bool  $expectedResult
     */
    public function testIsBrakeTestRecorded($brakeTestResult, $expectedResult)
    {
        $motTestDto = (new MotTestDto())->setBrakeTestResult($brakeTestResult);
        $motTestResults = new MotTestResults($motTestDto);
        $this->assertEquals($expectedResult, $motTestResults->isBrakeTestRecorded());
    }

    /**
     * @return array
     */
    public function isOriginalBrakeTestRecordedDataProvider()
    {
        return [
            [null, false, false],
            [[], false, false],
            [['generalPass' => null], false, false],
            [['generalPass' => 'undefined'], false, false],
            [['generalPass' => false], false, true],
            [['generalPass' => true], false, true],
            [null, true, false],
            [[], true, false],
            [['generalPass' => null], true, false],
            [['generalPass' => 'undefined'], true, false],
            [['generalPass' => false], true, false],
            [['generalPass' => true], true, false],
        ];
    }

    /**
     * @dataProvider isOriginalBrakeTestRecordedDataProvider
     *
     * @param mixed $originalBrakeTestResult
     * @param bool  $isOriginalMotTest
     * @param bool  $expectedResult
     */
    public function testIsOriginalBrakeTestRecorded($originalBrakeTestResult, $isOriginalMotTest, $expectedResult)
    {
        $motTestDto = new MotTestDto();
        if (!$isOriginalMotTest) {
            $motTestDto->setMotTestOriginal((new MotTestDto())->setBrakeTestResult($originalBrakeTestResult));
        }
        $motTestResults = new MotTestResults($motTestDto);

        $this->assertEquals($expectedResult, $motTestResults->isOriginalBrakeTestRecorded());
    }

    public function testIsBrakePerformanceNotTested()
    {
        foreach ([true, false] as $isBrakePerformanceNotTested) {
            $motTestDto = new MotTestDto();
            $motTestDto->setTesterBrakePerformanceNotTested($isBrakePerformanceNotTested);
            $motTestResults = new MotTestResults($motTestDto);

            $this->assertEquals($isBrakePerformanceNotTested, $motTestResults->isBrakePerformanceNotTested());
        }
    }

    /**
     * @return array
     */
    public function getBrakeTestOutcomeDataProvider()
    {
        return [
            [
                null, null, false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED
            ],
            [
                null, [], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED
            ],
            [
                null, ['generalPass' => 'undefined'], false, MotTestTypeCode::NORMAL_TEST,
                ''
            ],
            [
                null, ['generalPass' => true], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_PASSED
            ],
            [
                null, ['generalPass' => false], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_FAILED
            ],
            [
                ['generalPass' => true], null, false, MotTestTypeCode::RE_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_PASSED
            ],
            [
                ['generalPass' => true], [], false, MotTestTypeCode::RE_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_PASSED
            ],
            [
                ['generalPass' => true], ['generalPass' => 'undefined'], false, MotTestTypeCode::RE_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_PASSED
            ],
            [
                ['generalPass' => true], ['generalPass' => true], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_PASSED],
            [
                ['generalPass' => true], ['generalPass' => false], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_FAILED
            ],
            [
                ['generalPass' => false], null, false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED
            ],
            [
                ['generalPass' => false], [], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED
            ],
            [
                ['generalPass' => false], ['generalPass' => 'undefined'], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_FAILED
            ],
            [
                ['generalPass' => false], ['generalPass' => true], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_PASSED
            ],
            [
                ['generalPass' => false], ['generalPass' => false], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_FAILED
            ],
            [
                null, ['generalPass' => true], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                null, ['generalPass' => false], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => true], null, true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => true], [], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => true], ['generalPass' => 'undefined'], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => true], ['generalPass' => true], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => true], ['generalPass' => false], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => false], null, true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => false], [], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => false], ['generalPass' => 'undefined'], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => false], ['generalPass' => true], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
            [
                ['generalPass' => false], ['generalPass' => false], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED
            ],
        ];
    }

    /**
     * @dataProvider getBrakeTestOutcomeDataProvider
     *
     * @param mixed  $originalBrakeTestResult
     * @param mixed  $brakeTestResult
     * @param bool   $isBrakePerformanceNotTested
     * @param string $motTestTypeCode
     * @param bool   $expectedResult
     */
    public function testGetBrakeTestOutcome($originalBrakeTestResult, $brakeTestResult, $isBrakePerformanceNotTested,
                                            $motTestTypeCode, $expectedResult)
    {
        $motTestDto = new MotTestDto();
        $motTestDto->setBrakeTestResult($brakeTestResult);
        $motTestDto->setTesterBrakePerformanceNotTested($isBrakePerformanceNotTested);
        $motTestDto->setMotTestOriginal((new MotTestDto())->setBrakeTestResult($originalBrakeTestResult));
        $motTestDto->setTestType((new MotTestTypeDto())->setCode($motTestTypeCode));
        $motTestResults = new MotTestResults($motTestDto);

        $this->assertEquals($expectedResult, $motTestResults->getBrakeTestOutcome());
    }
}
