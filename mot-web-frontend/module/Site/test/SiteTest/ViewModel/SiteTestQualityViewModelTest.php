<?php
namespace SiteTest\ViewModel;

use DateTime;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Site\ViewModel\TestQuality\SiteTestQualityViewModel;

class SiteTestQualityViewModelTest extends \PHPUnit_Framework_TestCase
{
    const NATIONAL_AVERAGE = 'National average';

    const RETURN_LINK = '/vehicle-testing-station/1';
    const POSSIBLE_MONTHS_COUNT = 10;
    const CSV_FILE_SIZE_GROUP_A = 1001;
    const CSV_FILE_SIZE_GROUP_B = 1002;
    const IS_RETURN_TO_AE_TQI = false;

    /** @var  SiteTestQualityViewModel */
    private $siteTestQualityViewModel;

    public function setUp()
    {
        $date = new DateTime();
        $this->siteTestQualityViewModel = new SiteTestQualityViewModel(
            self::buildSitePerformanceDto(),
            self::buildNationalStatisticsPerformanceDto(),
            self::buildSiteDto(),
            $date,
            self::CSV_FILE_SIZE_GROUP_A,
            self::CSV_FILE_SIZE_GROUP_B,
            self::IS_RETURN_TO_AE_TQI
        );
    }

    public static function buildSitePerformanceDto()
    {
        $site = new SitePerformanceDto();

        $groupA = new SiteGroupPerformanceDto();

        $stats1 = new EmployeePerformanceDto();

        $stats1->setUsername("Tester");
        $stats1->setTotal(1);
        $stats1->setAverageTime(new TimeSpan(1, 1, 1, 1));
        $stats1->setPercentageFailed(100);

        $stats2 = new EmployeePerformanceDto();

        $stats2->setUsername("Tester 2");
        $stats2->setTotal(2);
        $stats2->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $stats2->setPercentageFailed(50.00);

        $groupA->setStatistics([$stats1, $stats2]);

        $totalA = (new MotTestingPerformanceDto())
            ->setAverageTime(new TimeSpan(2, 2, 2, 2))
            ->setTotal(2000)
            ->setPercentageFailed(10.10);

        $groupA->setTotal($totalA);

        $groupB = new SiteGroupPerformanceDto();

        $stats3 = new EmployeePerformanceDto();

        $stats3->setUsername("Tester 3");
        $stats3->setTotal(200);
        $stats3->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $stats3->setPercentageFailed(33.33);

        $groupB->setStatistics([$stats3]);

        $totalB = (new MotTestingPerformanceDto())
            ->setAverageTime(new TimeSpan(2, 2, 2, 2))
            ->setTotal(400)
            ->setPercentageFailed(0);

        $groupB->setTotal($totalB);

        $site->setA($groupA);
        $site->setB($groupB);

        return $site;
    }

    public static function buildNationalStatisticsPerformanceDto()
    {
        $national = new NationalPerformanceReportDto();
        $national->setMonth(4);
        $national->setYear(2016);

        $groupA = new MotTestingPerformanceDto();
        $groupA->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $groupA->setPercentageFailed(50);
        $groupA->setTotal(10);

        $national->setGroupA($groupA);

        $groupB = new MotTestingPerformanceDto();
        $groupB->setAverageTime(new TimeSpan(0, 0, 2, 2));
        $groupB->setPercentageFailed(30);
        $groupB->setTotal(5);

        $national->setGroupB($groupB);

        $national->getReportStatus()->setIsCompleted(true);

        return $national;
    }

    public static function buildEmptySitePerformanceDto()
    {
        $sitePerformanceDto = new SitePerformanceDto();
        $sitePerformanceDto->setA((new SiteGroupPerformanceDto())->setStatistics([])
            ->setTotal(new MotTestingPerformanceDto()));
        $sitePerformanceDto->setB((new SiteGroupPerformanceDto())->setStatistics([])
            ->setTotal(new MotTestingPerformanceDto()));;

        return $sitePerformanceDto;
    }

    public function testTablePopulatesWithRows()
    {
        $this->assertEquals(2, count($this->siteTestQualityViewModel->getA()->getTesterRows()));
        $this->assertEquals(1, count($this->siteTestQualityViewModel->getB()->getTesterRows()));
    }

    public function testTablePopulatesWithNationalAverage()
    {
        $this->assertEquals(self::NATIONAL_AVERAGE,
            $this->siteTestQualityViewModel->getA()->getNationalStatistic()->getName());

        $this->assertEquals(self::NATIONAL_AVERAGE,
            $this->siteTestQualityViewModel->getB()->getNationalStatistic()->getName());
    }

    public function testTablePopulatesWithNoTestersData()
    {
        $date = new DateTime();
        $this->siteTestQualityViewModel = new SiteTestQualityViewModel(
            self::buildEmptySitePerformanceDto(),
            self::buildNationalStatisticsPerformanceDto(),
            self::buildSiteDto(),
            $date,
            self::CSV_FILE_SIZE_GROUP_A,
            self::CSV_FILE_SIZE_GROUP_B,
            self::IS_RETURN_TO_AE_TQI
        );

        $this->assertFalse($this->siteTestQualityViewModel->getA()->hasTests());
        $this->assertFalse($this->siteTestQualityViewModel->getB()->hasTests());
    }

    private static function buildSiteDto()
    {

        $organisation = (new OrganisationDto())
            ->setId(1);
        $siteDto = new VehicleTestingStationDto();
        $siteDto->setTestClasses([1, 2])
            ->setOrganisation($organisation);

        return $siteDto;
    }

    public function testReturnLinkToAETQI()
    {
        $date = new DateTime();
        $this->siteTestQualityViewModel = new SiteTestQualityViewModel(
            self::buildEmptySitePerformanceDto(),
            self::buildNationalStatisticsPerformanceDto(),
            self::buildSiteDto(),
            $date,
            self::CSV_FILE_SIZE_GROUP_A,
            self::CSV_FILE_SIZE_GROUP_B,
            true
        );

        $this->assertEquals($this->siteTestQualityViewModel->getReturnLink()->getValue(), SiteTestQualityViewModel::RETURN_TO_AE_TQI);
    }

    public function testReturnLinkToVts()
    {
        $date = new DateTime();
        $this->siteTestQualityViewModel = new SiteTestQualityViewModel(
            self::buildEmptySitePerformanceDto(),
            self::buildNationalStatisticsPerformanceDto(),
            self::buildSiteDto(),
            $date,
            self::CSV_FILE_SIZE_GROUP_A,
            self::CSV_FILE_SIZE_GROUP_B,
            false
        );

        $this->assertEquals($this->siteTestQualityViewModel->getReturnLink()->getValue(), SiteTestQualityViewModel::RETURN_TO_VTS);
    }
}
