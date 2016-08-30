<?php


namespace ApplicationTest\Constants;


use DvsaCommon\Constants\BrakeTestConfigurationClass1And2;
use DvsaCommon\Enum\BrakeTestTypeCode;

class BrakeTestConfigurationClass1And2Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataProviderIsApplicableToTestType
     */
    public function testIsApplicableToTestType($testType, $isApplicable)
    {
        $this->assertEquals($isApplicable, BrakeTestConfigurationClass1And2::isLockApplicableToTestType($testType));
    }

    public function dataProviderIsApplicableToTestType()
    {
        return [
            [BrakeTestTypeCode::PLATE, true],
            [BrakeTestTypeCode::FLOOR, true],
            [BrakeTestTypeCode::ROLLER, true],
            [BrakeTestTypeCode::GRADIENT, false],
            [BrakeTestTypeCode::DECELEROMETER, false],
        ];
    }
}