<?php

namespace Core\Formatting;

class VehicleAgeFormatter
{
    const YEAR = 'year';
    const YEARS = 'years';

    const NOT_AVAILABLE_TEXT = 'Not available';
    private $notAvailableText;

    public function __construct($notAvailableText = self::NOT_AVAILABLE_TEXT)
    {
        $this->notAvailableText = $notAvailableText;
    }

    /**
     * @param float $vehicleAgeInMonths
     *
     * @return int
     *
     * @depracated This method is invalid. Foramtters should return strings not floats, ints. Also it should be static.
     */
    public static function calculateVehicleAge($vehicleAgeInMonths)
    {
        $years = (int) floor($vehicleAgeInMonths / 12);
        $months = $vehicleAgeInMonths % 12;
        if ($vehicleAgeInMonths <= 18) {
            return 1;
        } else {
            if ($months < 6) {
                return $years;
            } else {
                return $years + 1;
            }
        }
    }

    public function formatVehicleAge($vehicleAgeInMonths, $vehicleAgeAvailable = true)
    {
        // it is possible that the vehicles did how manufacture date set
        // then we usually show "Not available" message
        if (!$vehicleAgeAvailable) {
            return $this->notAvailableText;
        }

        // we display age in full years
        $vehicleAgeInYears = round($vehicleAgeInMonths / 12, 0, PHP_ROUND_HALF_UP);

        // Business wants to show "1" when the age in years is 0
        return $vehicleAgeInYears == 0
            ? '1'
            : (string) $vehicleAgeInYears;
    }

    /**
     * Returns "years" suffix.
     *
     * @param $numberOfYears
     *
     * @return string
     */
    public static function getYearSuffix($numberOfYears)
    {
        if (is_numeric($numberOfYears)) {
            if ($numberOfYears == 1) {
                return self::YEAR;
            } else {
                return self::YEARS;
            }
        } else {
            return $numberOfYears;
        }
    }
}
