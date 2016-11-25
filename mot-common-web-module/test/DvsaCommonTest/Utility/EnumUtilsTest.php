<?php

namespace DvsaCommonTest\Utility;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\EnumUtils;
use PHPUnit_Framework_TestCase;

class EnumUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testTesterTypeIsOutOfBandThrowsException()
    {
        EnumUtils::isTesterType(-1);
    }

    /**
     * @dataProvider cycleProviderNotATester
     *
     * @param $value String the MOT tester type
     */
    public function testCycleThroughEnumeratedValuesForNotATester($value)
    {
        $this->assertFalse(EnumUtils::isTesterType($value));
    }

    public function cycleProviderNotATester()
    {
        return [
            [MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS],
            [MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS],
            [MotTestTypeCode::TARGETED_REINSPECTION],
            [MotTestTypeCode::MOT_COMPLIANCE_SURVEY],
            [MotTestTypeCode::INVERTED_APPEAL],
            [MotTestTypeCode::STATUTORY_APPEAL],
            [MotTestTypeCode::OTHER],
            [MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING],
            [MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST],
            [MotTestTypeCode::NON_MOT_TEST]
        ];
    }

    /**
     * @dataProvider cycleProviderTesterTest
     *
     * @param $value String the MOT tester type
     */
    public function testCycleThroughEnumeratedValuesForTester($value)
    {
        $this->assertTrue(EnumUtils::isTesterType($value));
    }

    public function cycleProviderTesterTest()
    {
        return [
            [MotTestTypeCode::NORMAL_TEST],
            [MotTestTypeCode::RE_TEST],
            [MotTestTypeCode::MYSTERY_SHOPPER]
        ];
    }

}
