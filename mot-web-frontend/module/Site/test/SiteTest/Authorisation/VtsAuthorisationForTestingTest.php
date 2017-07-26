<?php


namespace SiteTest\Authorisation;


use DvsaCommon\Enum\VehicleClassCode;
use Site\Authorization\VtsAuthorisationForTesting;

class VtsAuthorisationForTestingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider classAtestDataProvider
     */
    public function testClassAAuthorisation($testClasses, $authorisedToTestGroupA)
    {
        $this->assertSame($authorisedToTestGroupA, VtsAuthorisationForTesting::canTestClass1Or2($testClasses));
    }

    public function classAtestDataProvider()
    {
        return [
            [[VehicleClassCode::CLASS_1], true],
            [[VehicleClassCode::CLASS_2], true],
            [[VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_2], true],
            [[VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_5], true],
            [[VehicleClassCode::CLASS_2, VehicleClassCode::CLASS_4], true],
            [
                [
                    VehicleClassCode::CLASS_1,
                    VehicleClassCode::CLASS_2,
                    VehicleClassCode::CLASS_3,
                    VehicleClassCode::CLASS_4,
                    VehicleClassCode::CLASS_5,
                    VehicleClassCode::CLASS_7
                ],
                true
            ],
            [[], false],
            [null, false],
            [[VehicleClassCode::CLASS_3], false],
            [[VehicleClassCode::CLASS_4], false],
            [[VehicleClassCode::CLASS_5], false],
            [[VehicleClassCode::CLASS_7], false],
            [[VehicleClassCode::CLASS_3, VehicleClassCode::CLASS_7], false],
            [[VehicleClassCode::CLASS_3, VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_5, VehicleClassCode::CLASS_7], false],
        ];
    }

    /**
     * @dataProvider classBtestDataProvider
     */
    public function testClassBAuthorisation($testClasses, $authorisedToTestGroupA)
    {
        $this->assertSame($authorisedToTestGroupA, VtsAuthorisationForTesting::canTestAnyOfClass3AndAbove($testClasses));
    }

    public function classBtestDataProvider()
    {
        return [
            [[VehicleClassCode::CLASS_3], true],
            [[VehicleClassCode::CLASS_4], true],
            [[VehicleClassCode::CLASS_5], true],
            [[VehicleClassCode::CLASS_7], true],
            [[VehicleClassCode::CLASS_3, VehicleClassCode::CLASS_4], true],
            [[VehicleClassCode::CLASS_3, VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_5, VehicleClassCode::CLASS_7], true],
            [
                [
                    VehicleClassCode::CLASS_1,
                    VehicleClassCode::CLASS_2,
                    VehicleClassCode::CLASS_3,
                    VehicleClassCode::CLASS_4,
                    VehicleClassCode::CLASS_5,
                    VehicleClassCode::CLASS_7
                ], true
            ],
            [[], false],
            [null, false],
            [[VehicleClassCode::CLASS_1], false],
            [[VehicleClassCode::CLASS_2], false],
            [[VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_2], false],
        ];
    }
}