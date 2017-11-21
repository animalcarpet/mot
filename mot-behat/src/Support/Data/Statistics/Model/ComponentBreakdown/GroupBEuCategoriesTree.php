<?php
namespace Dvsa\Mot\Behat\Support\Data\Statistics\Model\ComponentBreakdown;

use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB\GroupBEuReasonForRejection;

class GroupBEuCategoriesTree
{
    const ALEX_WHEELS_TYRES_AND_SUSPENSION = 20218;
    const BODY_STRUCTURE_AND_ATTACHMENTS = 20310;
    const BRAKES = 20003;
    const IDENTIFICATION_OF_THE_VEHICLE = 20000;
    const LAMPS_REFLECTORS_AND_ELECTRICAL_EQUIPMENT = 20158;
    const NON_COMPONENT_ADVISORIES = 10000;
    const NUISANCE = 20385;
    const OTHER_EQUIPMENT = 20365;
    const SEAT_BELT_INSTALLATION_CHECK = 20431;
    const STEERING = 20106;
    const SUPLEMENTARY_TESTS_FOR_BUSES_AND_COACHES = 20422;
    const VISIBILITY = 20147;

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
            self::ALEX_WHEELS_TYRES_AND_SUSPENSION => [
                GroupBEuReasonForRejection::AXLE_FRACTURED,
                GroupBEuReasonForRejection::AXLE_INSECURE,
                GroupBEuReasonForRejection::AXLE_WITH_LOOSE_FIXING_BOLTS,
                GroupBEuReasonForRejection::AXLE_HAS_EXCESSIVE_VERTICAL_MOVEMENT,
            ],
            self::BODY_STRUCTURE_AND_ATTACHMENTS => [
                GroupBEuReasonForRejection::DRIVERS_DOOR_LIKELY_TO_OPEN_INADVERTENTLY,
                GroupBEuReasonForRejection::DRIVERS_DOOR_HINGE_MISSING,
                GroupBEuReasonForRejection::DRIVERS_DOOR_CATCH_MISSING,
                GroupBEuReasonForRejection::DRIVERS_DOOR_PILLAR_DETERIORATED,
            ],

            self::BRAKES => [
                GroupBEuReasonForRejection::POWER_STEERING_PUMP_FRACTURED,
            ],
            self::IDENTIFICATION_OF_THE_VEHICLE => [

            ],
            self::LAMPS_REFLECTORS_AND_ELECTRICAL_EQUIPMENT => [

            ],
            self::NUISANCE => [

            ],
            self::OTHER_EQUIPMENT => [

            ],
            self::SEAT_BELT_INSTALLATION_CHECK => [

            ],
            self::STEERING => [
                GroupBEuReasonForRejection::FRACTURED,
                GroupBEuReasonForRejection::DEFORMED,
                GroupBEuReasonForRejection::MODIFICATION_UNSAFE,
                GroupBEuReasonForRejection::FRACTURED_TO_THE_EXTENT_THAT_STEERING_IS_AFFECTED,
                GroupBEuReasonForRejection::POWER_STEERING_PUMP_INSECURE_AND_STEERING_ADVERSELY_AFFECTED,
                GroupBEuReasonForRejection::POWER_STEERING_PUMP_LEAKING,
            ],
            self::SUPLEMENTARY_TESTS_FOR_BUSES_AND_COACHES => [

            ],
            self::VISIBILITY => [

            ]
        ];
    }
}
