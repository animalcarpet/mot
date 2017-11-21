<?php
namespace Dvsa\Mot\Behat\Support\Data\Statistics\Model\ComponentBreakdown;

use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB\GroupBPreEuDirectiveReasonForRejection;

class GroupBCategoriesTree
{
    const BODY_STRUCTURE_AND_GENERAL_ITEMS = 5690;
    const BRAKES = 5430;
    const DRIVERS_VIEW_OF_THE_ROAD = 5750;
    const DRIVING_CONTROLS_AND_SPEED_LIMITERS = 5780;
    const EXHAUST_FUEL_AND_EMISSIONS = 5730;
    const LAMPS_REFLECTORS_AND_ELECTRICAL_EQUIPMENT = 5000;
    const MOTOR_TRICYCLES_AND_QUADRICYCLES = 9000;
    const REGISTRATION_PLATES_AND_VIN = 5785;
    const ROAD_WHEELS = 5670;
    const SEAT_BELTS_AND_SUPPLEMENTARY_RESTRAINT_SYSTEMS = 5680;
    const STEERING = 5100;
    const SUSPENSION = 5190;
    const TOWBARS = 501;
    const TYRES = 5650;

    public static function getCategoryByRfrId($rfrId)
    {
        $tree = static::get();
        $categoryId = null;
        foreach ($tree as $category => $rfrs) {
            if (in_array($rfrId, $rfrs)) {
                $categoryId = $category;
                break;
            }
        }

        if ($categoryId === null) {
            throw new \InvalidArgumentException(sprintf("Category for rfr '%s' not found", $rfrId));
        }

        return $categoryId;
    }

    public static function get()
    {
        return [
            self::BODY_STRUCTURE_AND_GENERAL_ITEMS => [
                GroupBPreEuDirectiveReasonForRejection::BODY_HAS_EXCESSIVE_DISPLACEMENT_WHICH_MAY_LEAD_TO_LOSS_OF_VEHICLE_CONTROL,
                GroupBPreEuDirectiveReasonForRejection::BODY_INSECURE_AND_DANGEROUS_TO_OTHER_ROAD_USERS,
                GroupBPreEuDirectiveReasonForRejection::BONNET_RETAINING_DEVICE_INSECURE,
                GroupBPreEuDirectiveReasonForRejection::DRIVERS_DOOR_CATCH_MISSING,
                GroupBPreEuDirectiveReasonForRejection::FRONT_PASSENGER_DOOR_PILLAR_INSECURE,
            ],
            self::BRAKES => [
                GroupBPreEuDirectiveReasonForRejection::ANTI_LOCK_BRAKING_SYSTEM_COMPONENT_MISSING,
                GroupBPreEuDirectiveReasonForRejection::ANTI_LOCK_BRAKING_SYSTEM_EXCESSIVELY_DAMAGED,
                GroupBPreEuDirectiveReasonForRejection::ANTI_LOCK_BRAKING_SYSTEM_INAPPROPRIATELY_REPAIRED,
                GroupBPreEuDirectiveReasonForRejection::ANTI_LOCK_BRAKING_SYSTEM_INAPPROPRIATELY_MODIFIED,
                GroupBPreEuDirectiveReasonForRejection::ANTI_LOCK_BRAKING_SYSTEM_WARNING_LAMP_IS_MISSING,
            ],
            self::DRIVERS_VIEW_OF_THE_ROAD => [],
            self::DRIVING_CONTROLS_AND_SPEED_LIMITERS => [],
            self::EXHAUST_FUEL_AND_EMISSIONS => [],
            self::LAMPS_REFLECTORS_AND_ELECTRICAL_EQUIPMENT => [],
            self::MOTOR_TRICYCLES_AND_QUADRICYCLES => [],
            self::REGISTRATION_PLATES_AND_VIN => [],
            self::ROAD_WHEELS => [],
            self::SEAT_BELTS_AND_SUPPLEMENTARY_RESTRAINT_SYSTEMS => [],
            self::STEERING => [],
            self::SUSPENSION => [],
            self::TOWBARS => [],
            self::TYRES => [
                GroupBPreEuDirectiveReasonForRejection::TYRE_HAS_PLY_OR_CORDS_EXPOSED
            ],
        ];
    }
}
