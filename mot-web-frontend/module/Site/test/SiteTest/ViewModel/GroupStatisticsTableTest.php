<?php


namespace SiteTest\ViewModel;


use Core\Formatting\VehicleAgeFormatter;
use DateTime;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Site\ViewModel\TestQuality\GroupStatisticsTable;

class GroupStatisticsTableTest extends \PHPUnit_Framework_TestCase
{
    const AVERAGE_VEHICLE_AGE_SITE = 200;
    const AVERAGE_VEHICLE_AGE_NATIONAL = 100;

    /**
     * @dataProvider dateProviderProperVehicleAge
     */
    public function testProperVehicleAgeFormatting(
        SiteGroupPerformanceDto $sitePerformanceDto,
        MotTestingPerformanceDto $nationalPerformanceDto,
        $expectedSiteAverage,
        $expectedNationalAverage
    ) {
        $site = new VehicleTestingStationDto();
        $dateTime = new Datetime();
        $table = new GroupStatisticsTable(
            $sitePerformanceDto,
            true,
            $nationalPerformanceDto,
            'A',
            'asdasd',
            'A',
            $site,
            $dateTime,
            1000
        );
        $this->assertEquals($expectedSiteAverage, $table->getAverageVehicleAge());
        $this->assertEquals($expectedNationalAverage, $table->getNationalStatistic()->getAverageVehicleAge());
    }


    public static function buildEmptySitePerformanceDto()
    {
        $sitePerformanceDto = new SiteGroupPerformanceDto();
        $sitePerformanceDto->setStatistics([]);
        $sitePerformanceDto->setTotal(new MotTestingPerformanceDto());

        return $sitePerformanceDto;
    }

    protected static function buildNotEmptySiteDto()
    {
        $notEmptySite = self::buildEmptySitePerformanceDto();
        $notEmptySite->getTotal()->setIsAverageVehicleAgeAvailable(true);
        $notEmptySite->setStatistics([(new EmployeePerformanceDto())->setAverageTime(new TimeSpan(0, 0, 0, 0))]);
        $notEmptySite->getTotal()->setAverageVehicleAgeInMonths(self::AVERAGE_VEHICLE_AGE_SITE);
        $notEmptySite->getTotal()->setAverageTime(new TimeSpan(0, 0, 0, 0));

        return $notEmptySite;
    }

    protected static function buildNotEmptyNationalDto()
    {
        $notEmptyNational = self::buildNationalStatisticsPerformanceDto();
        $notEmptyNational->setTotal(300);
        $notEmptyNational->setAverageVehicleAgeInMonths(self::AVERAGE_VEHICLE_AGE_NATIONAL);
        $notEmptyNational->setIsAverageVehicleAgeAvailable(true);
        $notEmptyNational->setAverageTime(new Timespan(0, 0, 0, 0));

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
                self::buildEmptySitePerformanceDto(),
                self::buildNationalStatisticsPerformanceDto(),
                GroupStatisticsTable::TEXT_NOT_AVAILABLE,
                ''
            ],
            [
                self::buildNotEmptySiteDto(),
                $nationalWithTestsOnlyWithoutManufactureDate,
                VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_SITE),
                GroupStatisticsTable::TEXT_NOT_AVAILABLE
            ],
            [
                self::buildNotEmptySiteDto(),
                self::buildNotEmptyNationalDto(),
                VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_SITE),
                VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_NATIONAL)
            ],
        ];
    }
}
