<?php


namespace CoreTest\Formatter;


use Core\Formatting\VehicleAgeFormatter;

class VehicleAgeFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderVehicleAgeFormatting
     */
    public function testVehicleAgeFormatting($numberOfMoths, $expectedYears)
    {
        $this->assertEquals($expectedYears, VehicleAgeFormatter::calculateVehicleAge($numberOfMoths));
    }
    /**
     * @dataProvider dataProviderVehicleYearSuffix
     */
    public function testVehicleAgeInYearsSuffix($numberOfYears, $expectedYearsSuffix)
    {
        $this->assertEquals($expectedYearsSuffix, VehicleAgeFormatter::getYearSuffix($numberOfYears));
    }

    public function dataProviderVehicleAgeFormatting()
    {
        return [
            [-1, 1],
            [null, 1],
            [false, 1],
            [0, 1],
            [1, 1],
            [17, 1],
            [18, 1],
            [19, 2],
            [2 * 12 + 5, 2],
            [2 * 12 + 6, 3],
            [3 * 12 + 5, 3],
            [3 * 12 + 6, 4],
            [99 * 12 + 5, 99],
            [99 * 12 + 6, 100],
        ];
    }

    public function dataProviderVehicleYearSuffix()
    {
        return [
            [0, VehicleAgeFormatter::YEARS],
            [1, VehicleAgeFormatter::YEAR],
            [2, VehicleAgeFormatter::YEARS],
            [3, VehicleAgeFormatter::YEARS],
            [6, VehicleAgeFormatter::YEARS],
            [9, VehicleAgeFormatter::YEARS],
            [10, VehicleAgeFormatter::YEARS],
            [1100, VehicleAgeFormatter::YEARS],
        ];
    }
}