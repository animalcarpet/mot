<?php
namespace DvsaCommon\Constants;


use DvsaCommon\Model\VehicleClassGroup;

class ManualsFiles
{
    const GROUP_A_SECTION_0 = "Section-0-Identification-of-the-vehicle";
    const GROUP_A_SECTION_1 = "Section-1-Brakes";
    const GROUP_A_SECTION_2 = "Section-2-Steering";
    const GROUP_A_SECTION_3 = "Section-3-Visibility";
    const GROUP_A_SECTION_4 = "Section-4-Lamps-reflectors-and-electrical-equipment";
    const GROUP_A_SECTION_5 = "Section-5-Axles-Wheels-Tyres-and-Suspension";
    const GROUP_A_SECTION_6 = "Section-6-Body-Structure-and-Attachments";
    const GROUP_A_SECTION_7 = "Section-7-Other-equipment";
    const GROUP_A_SECTION_8 = "Section-8-Nuisance";
    const GROUP_A_SECTION_9 = "Section-9-Supplementary-tests-for-buses-and-coaches";
    const GROUP_A_SECTION_10 = "Section-10-Seat-belt-installation-checks";

    const GROUP_B_SECTION_0 = "Section-0-Identification-of-the-vehicle";
    const GROUP_B_SECTION_1 = "Section-1-Brakes";
    const GROUP_B_SECTION_2 = "Section-2-Steering";
    const GROUP_B_SECTION_3 = "Section-3-Visibility";
    const GROUP_B_SECTION_4 = "Section-4-Lamps-reflectors-and-electrical-equipment";
    const GROUP_B_SECTION_5 = "Section-5-Axles-Wheels-Tyres-and-Suspension";
    const GROUP_B_SECTION_6 = "Section-6-Body-Structure-and-Attachments";
    const GROUP_B_SECTION_7 = "Section-7-Other-equipment";
    const GROUP_B_SECTION_8 = "Section-8-Nuisance";
    const GROUP_B_SECTION_9 = "Section-9-Supplementary-tests-for-buses-and-coaches";
    const GROUP_B_SECTION_10 = "Section-10-Seat-belt-installation-checks";

    const GROUP_A = [
        self::GROUP_A_SECTION_0,
        self::GROUP_A_SECTION_1,
        self::GROUP_A_SECTION_2,
        self::GROUP_A_SECTION_3,
        self::GROUP_A_SECTION_4,
        self::GROUP_A_SECTION_5,
        self::GROUP_A_SECTION_6,
        self::GROUP_A_SECTION_7,
        self::GROUP_A_SECTION_8,
        self::GROUP_A_SECTION_9,
        self::GROUP_A_SECTION_10,
    ];

    const GROUP_B = [
        self::GROUP_B_SECTION_0,
        self::GROUP_B_SECTION_1,
        self::GROUP_B_SECTION_2,
        self::GROUP_B_SECTION_3,
        self::GROUP_B_SECTION_4,
        self::GROUP_B_SECTION_5,
        self::GROUP_B_SECTION_6,
        self::GROUP_B_SECTION_7,
        self::GROUP_B_SECTION_8,
        self::GROUP_B_SECTION_9,
        self::GROUP_B_SECTION_10,
    ];

    public static function getFileNameFromMap(string $vehicleClassCode, int $sectionId)
    {
        $fileName = "";

        if (VehicleClassGroup::isGroupA($vehicleClassCode)){
            $fileName = self::GROUP_A[$sectionId] ?? $fileName;
        } else {
            $fileName = self::GROUP_B[$sectionId] ?? $fileName;
        }

        return $fileName;
    }
}