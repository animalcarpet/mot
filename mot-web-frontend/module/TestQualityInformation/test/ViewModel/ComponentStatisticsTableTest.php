<?php
namespace Dvsa\Mot\Frontend\TestQualityInformationTest\ViewModel;


use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\ComponentStatisticsTable;
use Site\ViewModel\TestQuality\GroupStatisticsTable;

class ComponentStatisticsTableTest extends \PHPUnit_Framework_TestCase
{
    const AVERAGE_VEHICLE_AGE_USER = 200;
    const PERCENTAGE_FAILED = 66.545345234234;

    /**
     * @dataProvider dataProviderProperVehicleAge
     */
    public function testProperVehicleAgeFormatting(
        ComponentBreakdownDto $sitePerformanceDto, NationalComponentStatisticsDto $nationalPerformanceDto,
        $expectedSiteAverage
    )
    {
        $table = new ComponentStatisticsTable($sitePerformanceDto, $nationalPerformanceDto, 'A', 'asdasd', 'A');
        $this->assertEquals($expectedSiteAverage, $table->getAverageVehicleAge());
    }

    /**
     * @dataProvider dataProviderAverageFailedCount
     */
    public function testProperFailurePercentageRounding($expectedAverage,
        ComponentBreakdownDto $sitePerformanceDto, NationalComponentStatisticsDto $nationalPerformanceDto
    )
    {
        $table = new ComponentStatisticsTable($sitePerformanceDto, $nationalPerformanceDto, 'A', 'asdasd', 'A');
        $this->assertEquals($expectedAverage, $table->getFailurePercentage());
    }

    /**
     * @dataProvider dataProviderAverageTestDuration
     */
    public function testProperAverageTestDuration($expectedAverage,
        ComponentBreakdownDto $sitePerformanceDto, NationalComponentStatisticsDto $nationalPerformanceDto
    )
    {
        $table = new ComponentStatisticsTable($sitePerformanceDto, $nationalPerformanceDto, 'A', 'asdasd', 'A');
        $this->assertEquals($expectedAverage, $table->getAverageTestDuration());
    }

    public static function buildEmptyComponentBreakdown()
    {
        $componentBreakdownDto = new ComponentBreakdownDto();
        $componentBreakdownDto->setGroupPerformance(new MotTestingPerformanceDto());
        $componentBreakdownDto->setComponents([]);

        return $componentBreakdownDto;
    }

    protected static function buildNotEmptyComponentBreakdown()
    {
        $componentBreakdownDto = self::buildEmptyComponentBreakdown();
        $componentBreakdownDto->setGroupPerformance((new MotTestingPerformanceDto())
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(self::AVERAGE_VEHICLE_AGE_USER)
            ->setPercentageFailed(self::PERCENTAGE_FAILED)
        );

        return $componentBreakdownDto;
    }

    public static function buildNationalComponentStatisticsDto()
    {
        $national = new NationalComponentStatisticsDto();
        $national->setComponents([]);

        return $national;
    }

    public function dataProviderProperVehicleAge()
    {
        $avgAgeInYears = VehicleAgeFormatter::calculateVehicleAge(self::AVERAGE_VEHICLE_AGE_USER);

        return [
            [
                self::buildEmptyComponentBreakdown(),
                self::buildNationalComponentStatisticsDto(),
                GroupStatisticsTable::TEXT_NOT_AVAILABLE,
            ],
            [
                self::buildNotEmptyComponentBreakdown(),
                self::buildNationalComponentStatisticsDto(),
                $avgAgeInYears . ' ' . VehicleAgeFormatter::getYearSuffix($avgAgeInYears),
            ],
        ];
    }

    public function dataProviderAverageFailedCount()
    {
        return [
            [
                round(static::PERCENTAGE_FAILED) . '%',
                static::buildNotEmptyComponentBreakdown(),
                static::buildNationalComponentStatisticsDto(),
            ],
            [
                ComponentStatisticsTable::TEXT_EMPTY,
                static::buildEmptyComponentBreakdown(),
                static::buildNationalComponentStatisticsDto(),
            ],
        ];
    }

    public function dataProviderAverageTestDuration()
    {
        $notEmptyBreakdown = static::buildEmptyComponentBreakdown();
        $notEmptyBreakdown->getGroupPerformance()->setAverageTime(new TimeSpan(0, 1, 42, 0));

        return [
            [
                ComponentStatisticsTable::TEXT_EMPTY,
                static::buildNotEmptyComponentBreakdown(),
                static::buildNationalComponentStatisticsDto(),
            ],
            [
                1 * 60 + 42,
                $notEmptyBreakdown,
                static::buildNationalComponentStatisticsDto(),
            ],
        ];
    }
}