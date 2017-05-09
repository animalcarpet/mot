<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaMotTestTest\TestHelper\Fixture;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\MotTestResults;
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
        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
        if ($brakeTestResult === null || empty($brakeTestResult)) {
            $testMotTestData->brakeTestResult = $brakeTestResult;
        } else {
            $testMotTestData->brakeTestResult = (object) $brakeTestResult;
        }

        $motTest = new MotTest($testMotTestData);

        $motTestResults = new MotTestResults($motTest, null);
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
            [[], true, false],
            [['generalPass' => null], true, false],
            [['generalPass' => 'undefined'], true, false],
            [['generalPass' => false], true, true],
            [['generalPass' => true], true, true],
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
        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
        $testMotTestData->brakeTestResult = new \StdClass();

        if (!$isOriginalMotTest) {
            $originalMotTestTest = Fixture::getMotTestDataVehicleClass4(true);
            if ($originalBrakeTestResult === null || empty($originalBrakeTestResult)) {
                $originalMotTestTest->brakeTestResult = $originalBrakeTestResult;
            } else {
                $originalMotTestTest->brakeTestResult = (object) $originalBrakeTestResult;
            }
            $motTestResults = new MotTestResults(new MotTest($testMotTestData), new MotTest($originalMotTestTest));
        } else {
            $originalMotTestTest = Fixture::getMotTestDataVehicleClass4(true);
            $originalMotTestTest->motTestOriginalNumber = null;
            if ($originalBrakeTestResult === null || empty($originalBrakeTestResult)) {
                $originalMotTestTest->brakeTestResult = $originalBrakeTestResult;
            } else {
                $originalMotTestTest->brakeTestResult = (object) $originalBrakeTestResult;
            }
            $motTestResults = new MotTestResults(new MotTest($testMotTestData), new MotTest($originalMotTestTest));
        }

        $this->assertEquals($expectedResult, $motTestResults->isOriginalBrakeTestRecorded());
    }

    public function testIsOriginalBrakeTestRecordedWhereOriginalTestIsNull()
    {
        $motTestResults = new MotTestResults(new MotTest(new \StdClass()), null);

        $this->assertEquals(false, $motTestResults->isOriginalBrakeTestRecorded());
    }

    public function testIsBrakePerformanceNotTested()
    {
        foreach ([true, false] as $isBrakePerformanceNotTested) {
            $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
            $testMotTestData->testerBrakePerformanceNotTested = $isBrakePerformanceNotTested;
            $motTestResults = new MotTestResults(new MotTest($testMotTestData));

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
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED,
            ],
            [
                null, [], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED,
            ],
            [
                null, ['generalPass' => 'undefined'], false, MotTestTypeCode::NORMAL_TEST,
                '',
            ],
            [
                null, ['generalPass' => true], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_PASSED,
            ],
            [
                null, ['generalPass' => false], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_FAILED,
            ],
            [
                ['generalPass' => true], null, false, MotTestTypeCode::RE_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_PASSED,
            ],
            [
                ['generalPass' => true], [], false, MotTestTypeCode::RE_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_PASSED,
            ],
            [
                ['generalPass' => true], ['generalPass' => 'undefined'], false, MotTestTypeCode::RE_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_PASSED,
            ],
            [
                ['generalPass' => true], ['generalPass' => true], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_PASSED, ],
            [
                ['generalPass' => true], ['generalPass' => false], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_FAILED,
            ],
            [
                ['generalPass' => false], null, false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED,
            ],
            [
                ['generalPass' => false], [], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_RECORDED,
            ],
            [
                ['generalPass' => false], ['generalPass' => 'undefined'], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::INITIAL_BRAKE_TEST_OUTCOME_FAILED,
            ],
            [
                ['generalPass' => false], ['generalPass' => true], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_PASSED,
            ],
            [
                ['generalPass' => false], ['generalPass' => false], false, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_FAILED,
            ],
            [
                null, ['generalPass' => true], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                null, ['generalPass' => false], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => true], null, true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => true], [], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => true], ['generalPass' => 'undefined'], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => true], ['generalPass' => true], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => true], ['generalPass' => false], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => false], null, true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => false], [], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => false], ['generalPass' => 'undefined'], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => false], ['generalPass' => true], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
            ],
            [
                ['generalPass' => false], ['generalPass' => false], true, MotTestTypeCode::NORMAL_TEST,
                MotTestResults::BRAKE_TEST_OUTCOME_NOT_TESTED,
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
        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
        $testMotTestData->testerBrakePerformanceNotTested = $isBrakePerformanceNotTested;
        $testMotTestData->testTypeCode = $motTestTypeCode;
        if ($brakeTestResult === null || empty($brakeTestResult)) {
            $testMotTestData->brakeTestResult = $brakeTestResult;
        } else {
            $testMotTestData->brakeTestResult = (object) $brakeTestResult;
        }

        $originalMotTestTest = Fixture::getMotTestDataVehicleClass4(true);

        $originalMotTestTest->motTestOriginalNumber = '12345';
        if ($originalBrakeTestResult === null || empty($originalBrakeTestResult)) {
            $originalMotTestTest->brakeTestResult = $originalBrakeTestResult;
        } else {
            $originalMotTestTest->brakeTestResult = (object) $originalBrakeTestResult;
        }

        $motTestData = new MotTest($testMotTestData);
        $originalMotTestResult = new MotTest($originalMotTestTest);
        $motTestResults = new MotTestResults($motTestData, $originalMotTestResult);

        $this->assertEquals($expectedResult, $motTestResults->getBrakeTestOutcome());
    }
}
