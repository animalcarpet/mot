<?php

namespace PersonModule\ViewModel;

use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\GroupStatisticsTable;

class GroupStatisticsTableTest extends \PHPUnit_Framework_TestCase
{
    const AVERAGE_VEHICLE_AGE_EMPLOYEE = 200;
    const AVERAGE_VEHICLE_AGE_NATIONAL = 100;
    const USER_ID = 1;

    /**
     * @dataProvider dateProviderProperVehicleAge
     * @param EmployeePerformanceDto $employeePerformanceDto
     * @param MotTestingPerformanceDto $nationalPerformanceDto
     * @param $expectedEmployeeAverage
     * @param $expectedNationalAverage
     */
    public function testProperVehicleAgeFormatting(
        EmployeePerformanceDto $employeePerformanceDto,
        MotTestingPerformanceDto $nationalPerformanceDto,
        $expectedEmployeeAverage,
        $expectedNationalAverage
    ) {
        $table = new GroupStatisticsTable(
            $employeePerformanceDto,
            [],
            true,
            $nationalPerformanceDto,
            'description',
            'A',
            self::USER_ID
        );
        $this->assertEquals($expectedEmployeeAverage, $table->getAverageGroupStatisticsHeader()->getAverageVehicleAge());
        $this->assertEquals($expectedNationalAverage, $table->getNationalAverageVehicleAge());
    }

    public static function buildEmptyEmployeePerformanceDto()
    {
        $employeePerformanceDto = new EmployeePerformanceDto();
        $employeePerformanceDto->setTotal(new MotTestingPerformanceDto());
        $employeePerformanceDto->setTotal(new MotTestingPerformanceDto());
        $employeePerformanceDto->setAverageTime(new TimeSpan(0, 0, 0, 0));

        return $employeePerformanceDto;
    }

    protected static function buildNotEmptyEmployeeDto()
    {
        $notEmptyEmployee = self::buildEmptyEmployeePerformanceDto();
        $notEmptyEmployee->setIsAverageVehicleAgeAvailable(true);
        $notEmptyEmployee->setAverageVehicleAgeInMonths(self::AVERAGE_VEHICLE_AGE_EMPLOYEE);

        return $notEmptyEmployee;
    }

    protected static function buildNotEmptyNationalDto()
    {
        $notEmptyNational = self::buildNationalStatisticsPerformanceDto();
        $notEmptyNational->setTotal(300);
        $notEmptyNational->setAverageVehicleAgeInMonths(self::AVERAGE_VEHICLE_AGE_NATIONAL);
        $notEmptyNational->setAverageTime(new Timespan(1, 0, 0, 0));
        $notEmptyNational->setIsAverageVehicleAgeAvailable(true);

        return $notEmptyNational;
    }

    public static function buildNationalStatisticsPerformanceDto()
    {
        $national = new MotTestingPerformanceDto();

        return $national;
    }

    public function dateProviderProperVehicleAge()
    {
        $nationalWithTestsOnlyWithoutManufactureDate = self::buildNotEmptyNationalDto();
        $nationalWithTestsOnlyWithoutManufactureDate->setTotal(100);
        $nationalWithTestsOnlyWithoutManufactureDate->setIsAverageVehicleAgeAvailable(false);
        $nationalWithTestsOnlyWithoutManufactureDate->setAverageVehicleAgeInMonths(0);

        return [
            [
                self::buildEmptyEmployeePerformanceDto(),
                self::buildNationalStatisticsPerformanceDto(),
                null,
                '',
            ],
            [
                self::buildNotEmptyEmployeeDto(),
                $nationalWithTestsOnlyWithoutManufactureDate,
                VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_EMPLOYEE),
                0
            ],
            [
                self::buildNotEmptyEmployeeDto(),
                self::buildNotEmptyNationalDto(),
                VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_EMPLOYEE),
                VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_NATIONAL),
            ],
        ];
    }
}
