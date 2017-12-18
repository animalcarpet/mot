<?php

namespace DvsaCommon\ReasonForRejection;


use DvsaCommon\Constants\ManualsFiles;
use DvsaCommon\Enum\VehicleClassCode;

class InspectionManualReferenceUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    const URL_PREFIX = 'documents/manuals';

    public function dataProviderInspectionManualReferencesPreEuManuals()
    {
        return [
            ["1.2.3.4", VehicleClassCode::CLASS_1, sprintf("%s/m1s01000201.htm", self::URL_PREFIX)],
            ["1.2.3.4", VehicleClassCode::CLASS_4, sprintf("%s/m4s01000201.htm", self::URL_PREFIX)],
            ["10.20.30.40", VehicleClassCode::CLASS_4, sprintf("%s/m4s0100002001.htm", self::URL_PREFIX)],
            ["2.4A1b(ii)", VehicleClassCode::CLASS_4, sprintf("%s/m4s02000401.htm", self::URL_PREFIX)],
            ["10.20.30.40", "9",  sprintf("%s/m4s0100002001.htm", self::URL_PREFIX)],
            ["1.invalid-section", VehicleClassCode::CLASS_4, ""],
            ["invalid-section", VehicleClassCode::CLASS_4, ""],
            ["", VehicleClassCode::CLASS_4, ""],
        ];
    }

    public function dataProviderInspectionManualReferencesEuRoadworthinessManuals()
    {
        return [
            ["1.2.3.4", VehicleClassCode::CLASS_1, sprintf("%s/class12/%s.html#section_1.2.3.4", self::URL_PREFIX, ManualsFiles::GROUP_A_SECTION_1)],
            ["2.4A1b(ii)", VehicleClassCode::CLASS_1, sprintf("%s/class12/%s.html#section_2.4", self::URL_PREFIX, ManualsFiles::GROUP_A_SECTION_2)],
            ["1.2.3.4", VehicleClassCode::CLASS_4, sprintf("%s/class3457/%s.html#section_1.2.3.4", self::URL_PREFIX, ManualsFiles::GROUP_B_SECTION_1)],
            ["10.20.30.40", VehicleClassCode::CLASS_4, sprintf("%s/class3457/%s.html#section_10.20.30.40", self::URL_PREFIX, ManualsFiles::GROUP_B_SECTION_10)],
            ["90000", VehicleClassCode::CLASS_4, sprintf("%s/class3457/", self::URL_PREFIX)],
            ["10.20.30.40", "9", sprintf("%s/class3457/%s.html#section_10.20.30.40", self::URL_PREFIX, ManualsFiles::GROUP_B_SECTION_10)],
            ["1.invalid-section", VehicleClassCode::CLASS_4, sprintf("%s/class3457/", self::URL_PREFIX)],
            ["invalid-section", VehicleClassCode::CLASS_4, sprintf("%s/class3457/", self::URL_PREFIX)],
            ["", VehicleClassCode::CLASS_4, ""],
        ];
    }

    /** @dataProvider dataProviderInspectionManualReferencesPreEuManuals
     * @param string $inspectionManualReference
     * @param string $vehicleClassGroup
     * @param string $expectedUrl
     */
    public function test_buildPreEu(string $inspectionManualReference, string $vehicleClassGroup, string $expectedUrl)
    {
        $actualUrl = InspectionManualReferenceUrlBuilder::build($inspectionManualReference, $vehicleClassGroup, true);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /** @dataProvider dataProviderInspectionManualReferencesEuRoadworthinessManuals
     * @param string $inspectionManualReference
     * @param string $vehicleClassGroup
     * @param string $expectedUrl
     */
    public function test_buildForEuRoadworthiness(string $inspectionManualReference, string $vehicleClassGroup, string $expectedUrl)
    {
        $actualUrl = InspectionManualReferenceUrlBuilder::build($inspectionManualReference, $vehicleClassGroup, false);

        $this->assertEquals($expectedUrl, $actualUrl);
    }
}