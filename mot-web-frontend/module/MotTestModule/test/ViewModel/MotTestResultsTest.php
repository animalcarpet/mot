<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\MotTestResults;
use DvsaCommon\Dto\Common\MotTestDto;
use PHPUnit_Framework_TestCase;

class MotTestResultsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function isBrakeTestResultPassDataProvider()
    {
        return [
            [null, false],
            [[], false],
            [['generalPass' => null], false],
            [['generalPass' => 'undefined'], false],
            [['generalPass' => false], false],
            [['generalPass' => true], true],
        ];
    }

    /**
     * @dataProvider isBrakeTestResultPassDataProvider
     *
     * @param mixed $brakeTestResult
     * @param bool  $expectedResult
     */
    public function testIsBrakeTestResultPass($brakeTestResult, $expectedResult)
    {
        $motTestDto = (new MotTestDto())->setBrakeTestResult($brakeTestResult);
        $motTestResults = new MotTestResults($motTestDto);
        $this->assertEquals($expectedResult, $motTestResults->isBrakeTestResultPass());
    }

    /**
     * @return array
     */
    public function isBrakeTestRecordedDataProvider()
    {
        return [
            [null, false],
            [[], false],
            [['generalPass' => null], true],
            [['generalPass' => 'undefined'], true],
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
    public function getBrakeTestOutcomeDataProvider()
    {
        return [
            [null, MotTestResults::BRAKE_TEST_OUTCOME_FAILED],
            [[], MotTestResults::BRAKE_TEST_OUTCOME_FAILED],
            [['generalPass' => 'undefined'], MotTestResults::BRAKE_TEST_OUTCOME_FAILED],
            [['generalPass' => true], MotTestResults::BRAKE_TEST_OUTCOME_PASSED],
            [['generalPass' => false], MotTestResults::BRAKE_TEST_OUTCOME_FAILED],
        ];
    }

    /**
     * @dataProvider getBrakeTestOutcomeDataProvider
     *
     * @param mixed $brakeTestResult
     * @param bool  $expectedResult
     */
    public function testGetBrakeTestOutcome($brakeTestResult, $expectedResult)
    {
        $motTestDto = (new MotTestDto())->setBrakeTestResult($brakeTestResult);
        $motTestResults = new MotTestResults($motTestDto);
        $this->assertEquals($expectedResult, $motTestResults->getBrakeTestOutcome());
    }
}
