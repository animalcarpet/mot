<?php
namespace SiteTest\Mapper;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Site\Mapper\SiteTestQualityCsvMapper;

class SiteTestQualityCsvMapperTest extends \PHPUnit_Framework_TestCase
{
    const SITE_TESTER_NATIONAL_COLUMN = 3;
    const TESTS_DONE_COLUMN = 5;
    const AVERAGE_VEHICLE_AGE_COLUMN = 6;
    const AVERAGE_TEST_TIME_COLUMN = 7;
    const TESTS_FAILED_COLUMN = 8;

    const COLUMN_COUNT = 9;
    const VTS_NAME = 'SITE-NAME';
    const VTS_SITE_NUMBER = 'SITE-NUMBER';
    const MONTH = 5;
    const YEAR = 2016;

    /** @var  SiteTestQualityCsvMapper */
    private $sut;
    /** @var  SiteGroupPerformanceDto */
    private $siteGroupPerformanceDto;
    /** @var  MotTestingPerformanceDto */
    private $nationalGroupPerformanceDto;
    /** @var  VehicleTestingStationDto */
    private $vehicleTestingStationDto;
    /** @var  string */
    private $group;

    public function setUp()
    {
        $this->siteGroupPerformanceDto = $this->buildSiteGroupPerformanceDto(
            [10, 10, 66.66, 10],
            [
                [20, 10, 12.1, 33.33, 1, $this->getTesters()[0]],
                [10, 10, 16, 66.66, 1, $this->getTesters()[1]],
                [30, 10, 20.4, 99.99, 1, $this->getTesters()[2]],
            ]
        );

        $this->nationalGroupPerformanceDto = $this->buildNationalGroupPerformanceDto(
            [1, 2, 3, 12]
        );

        $this->vehicleTestingStationDto = $this->buildVehicleTestingStationDto();
        $this->group = VehicleClassGroupCode::BIKES;

        $this->sut = new SiteTestQualityCsvMapper(
            $this->siteGroupPerformanceDto, true, $this->nationalGroupPerformanceDto, $this->vehicleTestingStationDto, $this->group, self::MONTH, self::YEAR
        );
    }

    private function getTesters()
    {
        return [
            'tester1',
            'tester2',
            'tester3'
        ];
    }

    private function buildSiteGroupPerformanceDto(array $total, array $employeePerformance)
    {
        $dto = new SiteGroupPerformanceDto();
        $dto->setTotal($this->buildMotTestingPerformanceDto($total));

        $statistics = [];
        foreach ($employeePerformance as $employee) {
            $statistic = (new EmployeePerformanceDto())
                ->setTotal($employee[0])
                ->setAverageTime(new TimeSpan(0, 0, $employee[1], 0))
                ->setAverageVehicleAgeInMonths($employee[2])
                ->setPercentageFailed($employee[3])
                ->setPersonId($employee[4])
                ->setUsername($employee[5]);

            $statistics[] = $statistic;
        }
        $dto->setStatistics($statistics);

        return $dto;
    }

    private function buildNationalGroupPerformanceDto($motTestingPerformance)
    {
        return $this->buildMotTestingPerformanceDto($motTestingPerformance);
    }

    private function buildMotTestingPerformanceDto(array $motTestingPerformance)
    {
        $dto = new MotTestingPerformanceDto();
        $dto->setTotal($motTestingPerformance[0])
            ->setAverageTime(new TimeSpan(0, 0, $motTestingPerformance[1], 0))
            ->setPercentageFailed($motTestingPerformance[2])
            ->setAverageVehicleAgeInMonths($motTestingPerformance[3]);

        return $dto;
    }

    private function buildVehicleTestingStationDto()
    {
        $dto = new VehicleTestingStationDto();
        $dto->setName(self::VTS_NAME)
            ->setSiteNumber(self::VTS_SITE_NUMBER);

        return $dto;
    }

    public function testCsvFileHasCorrectColumnCount()
    {
        $csvFile = $this->sut->toCsvFile();

        $this->assertCount(self::COLUMN_COUNT, $csvFile->getHeaders());

        $rows = $csvFile->getRows();
        foreach ($rows as $row) {
            $this->assertCount(self::COLUMN_COUNT, $row);
        }
    }

    public function testLastRowHasNationalStatistics()
    {
        $csvFile = $this->sut->toCsvFile();

        $rows = $csvFile->getRows();
        $length = count($rows);

        $lastRow = $rows[$length - 1];

        $this->assertEquals('National', $lastRow[self::SITE_TESTER_NATIONAL_COLUMN]);
        $this->assertEquals(1, $lastRow[self::TESTS_DONE_COLUMN]);
        $this->assertEquals(2, $lastRow[self::AVERAGE_TEST_TIME_COLUMN]);
        $this->assertEquals('3%', $lastRow[self::TESTS_FAILED_COLUMN]);
        $this->assertEquals(1, $lastRow[self::AVERAGE_VEHICLE_AGE_COLUMN]);
    }

    public function testCorrectNamesArePopulated()
    {
        $csvFile = $this->sut->toCsvFile();

        $rows = $csvFile->getRows();
        $length = count($rows);

        foreach ($rows as $i => $row) {
            $nameColumn = $row[self::SITE_TESTER_NATIONAL_COLUMN];

            switch ($i) {
                case 0:
                    $this->assertEquals('Site', $nameColumn);
                    break;
                case $length - 1:
                    $this->assertEquals('National', $nameColumn);
                    break;
                default:
                    $this->assertEquals($this->getTesters()[$i - 1], $nameColumn);
            }
        }
    }

    public function testFailedPercentageIsRounded()
    {
        $csvFile = $this->sut->toCsvFile();

        $rows = $csvFile->getRows();

        $this->assertSame('67%', $rows[0][self::TESTS_FAILED_COLUMN]);
        $this->assertSame('33%', $rows[1][self::TESTS_FAILED_COLUMN]);
        $this->assertSame('67%', $rows[2][self::TESTS_FAILED_COLUMN]);
        $this->assertSame('100%', $rows[3][self::TESTS_FAILED_COLUMN]);
    }

    public function testAverageVehicleAgeIsRounded()
    {
        $csvFile = $this->sut->toCsvFile();

        $rows = $csvFile->getRows();

        $this->assertSame(1, $rows[0][self::AVERAGE_VEHICLE_AGE_COLUMN]);
        $this->assertSame(1, $rows[1][self::AVERAGE_VEHICLE_AGE_COLUMN]);
        $this->assertSame(1, $rows[2][self::AVERAGE_VEHICLE_AGE_COLUMN]);
        $this->assertSame(2, $rows[3][self::AVERAGE_VEHICLE_AGE_COLUMN]);
    }

    public function testFileNameIsCorrect()
    {
        $csvFile = $this->sut->toCsvFile();
        $csvFile->getFileName();
        $this->assertEquals("Test-quality-information_SITE-NAME_SITE-NUMBER_Group-A_May-2016.csv",
            $csvFile->getFileName());
    }
}