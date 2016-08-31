<?php

namespace Core\Formatting;


class VehicleAgeFormatter
{
    const YEAR = 'year';
    const YEARS = 'years';

    /**
     * @param float $vehicleAgeInMonths
     * @return int
     */
    public static function calculateVehicleAge($vehicleAgeInMonths)
    {
        $years = (int) floor($vehicleAgeInMonths / 12);
        $months = $vehicleAgeInMonths % 12;
        if ($vehicleAgeInMonths <= 18){
            return 1;
        } else {
            if($months < 6){
                return $years;
            } else {
                return $years + 1;
            }
        }
    }

    /**
     * Returns "years" suffix
     * @param $numberOfYears
     * @return string
     */
    public static function getYearSuffix($numberOfYears)
    {
        if(is_numeric($numberOfYears)){
            if($numberOfYears == 1){
                return self::YEAR;
            } else {
                return self::YEARS;
            }
        } else {
            return $numberOfYears;
        }
    }
}
