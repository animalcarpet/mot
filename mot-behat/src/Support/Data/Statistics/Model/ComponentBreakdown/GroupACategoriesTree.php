<?php
namespace Dvsa\Mot\Behat\Support\Data\Statistics\Model\ComponentBreakdown;

use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;

class GroupACategoriesTree
{
    const MOTORCYCLE_BODY_AND_STRUCTURE = 240;
    const MOTORCYCLE_BRAKES = 120;
    const MOTORCYCLE_DRIVE_SYSTEM = 260;
    const MOTORCYCLE_DRIVING_CONTROLS = 270;
    const MOTORCYCLE_FUEL_AND_EXHAUST = 220;
    const MOTORCYCLE_LIGHTING_AND_SIGNALLING = 1;
    const MOTORCYCLE_REG_PLATES_AND_VIN = 290;
    const MOTORCYCLE_SIDECAR = 195;
    const MOTORCYCLE_STEERING_AND_SUSPENSION = 50;
    const MOTORCYCLE_TYRES_AND_WHEELS = 172;

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
            self::MOTORCYCLE_BODY_AND_STRUCTURE => [

            ],
            self::MOTORCYCLE_BRAKES => [
                ReasonForRejectionGroupA::RFR_BRAKES_PERFORMANCE_GRADIENT,
                ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
            ],
            self::MOTORCYCLE_DRIVE_SYSTEM => [

            ],
            self::MOTORCYCLE_DRIVING_CONTROLS => [

            ],
            self::MOTORCYCLE_FUEL_AND_EXHAUST => [

            ],
            self::MOTORCYCLE_LIGHTING_AND_SIGNALLING => [
                ReasonForRejectionGroupA::RFR_POSITION_LAMPS_MOTORCYCLE_FRONT
            ],
            self::MOTORCYCLE_REG_PLATES_AND_VIN => [

            ],
            self::MOTORCYCLE_SIDECAR => [

            ],
            self::MOTORCYCLE_STEERING_AND_SUSPENSION => [

            ],
            self::MOTORCYCLE_TYRES_AND_WHEELS => [

            ]
        ];
    }
}
