<?php

namespace SiteTest\Mapper;

use Core\File\CsvFile;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Site\Mapper\UserTestQualityCsvMapper;

class UserTestQualityCsvMapperTest extends \PHPUnit_Framework_TestCase
{
    const GROUP = 'A';
    const MONTH = 6;
    const YEAR = 2016;
    const SITE_NAME = 'Test site name';
    const NOT_AVAILABLE = 'n/a';
    const SITE_ID = 'V1234';
    const COMPONENT_1_NAME = 'Component 1';
    const COMPONENT_2_NAME = 'Component 2';
    const TESTER_NAME = 'John Smith Tester';
    const TESTER_USER_ID = 'tester123';

    const COLUMN_COUNT = 13;

    const SITE_NAME_COLUMN = 0;
    const SITE_ID_COLUMN = 1;
    const SITE_DATE_COLUMN = 2;
    const TESTER_NATIONAL_COLUMN = 3;
    const TESTER_NAME_COLUMN = 4;
    const USER_ID_COLUMN = 5;
    const GROUP_COLUMN = 6;
    const TESTS_DONE_COLUMN = 7;
    const VEHICLE_AGE_COLUMN = 8;
    const TEST_TIME_COLUMN = 9;
    const TESTS_FAILED_COLUMN = 10;
    const COMPONENT_1_COLUMN = 11;
    const COMPONENT_2_COLUMN = 12;

    const TESTER_TESTS_DONE = 10;
    const NATIONAL_TESTS_DONE = 11;

    const TESTER_VEHICLE_AGE = 17;
    const NATIONAL_VEHICLE_AGE = 19;

    const EXPECTED_TESTER_VEHICLE_AGE = 1;
    const EXPECTED_NATIONAL_VEHICLE_AGE = 2;

    const TESTER_TEST_TIME = 30;
    const NATIONAL_TEST_TIME = 31;

    const TESTER_TESTS_FAILED = 45.1;
    const EXPECTED_TESTER_TESTS_FAILED = '45%';
    const NATIONAL_TESTS_FAILED = 45.9;
    const EXPECTED_NATIONAL_TESTS_FAILED = '46%';

    const TESTER_COMPONENT_1_FAIL_RATE = 33.3;
    const EXPECTED_TESTER_COMPONENT_1_FAIL_RATE = '33.3%';
    const NATIONAL_COMPONENT_1_FAIL_RATE = 34.3;
    const EXPECTED_NATIONAL_COMPONENT_1_FAIL_RATE = '34.3%';

    const TESTER_COMPONENT_2_FAIL_RATE = 66.6;
    const EXPECTED_TESTER_COMPONENT_2_FAIL_RATE = '66.6%';
    const NATIONAL_COMPONENT_2_FAIL_RATE = 67.6;
    const EXPECTED_NATIONAL_COMPONENT_2_FAIL_RATE = '67.6%';

    /** @var ComponentBreakdownDto */
    private $userBreakdownDto;
    /** @var NationalComponentStatisticsDto */
    private $nationalBreakdownDto;
    /** @var MotTestingPerformanceDto */
    private $nationalGroupPerformanceDto;
    /** @var VehicleTestingStationDto */
    private $vehicleTestingStationDto;
    /** @var UserTestQualityCsvMapper */
    private $sut;
    /** @var CsvFile */
    private $csvFile;

    public function setUp()
    {
        $this->userBreakdownDto = $this->buildComponentBreakdownDto(
            self::TESTER_NAME,
            self::TESTER_USER_ID,
            [
                $this->buildComponentDto(self::COMPONENT_1_NAME, 1, self::TESTER_COMPONENT_1_FAIL_RATE),
                $this->buildComponentDto(self::COMPONENT_2_NAME, 2, self::TESTER_COMPONENT_2_FAIL_RATE),
            ],
            $this->buildMotTestingPerformanceDto(
                self::TESTER_VEHICLE_AGE,
                self::TESTER_TESTS_FAILED,
                self::TESTER_TESTS_DONE,
                self::TESTER_TEST_TIME
            )
        );

        $this->nationalBreakdownDto = $this->buildNationalComponentStatisticsDto(
            [
                $this->buildComponentDto(self::COMPONENT_1_NAME, 1, self::NATIONAL_COMPONENT_1_FAIL_RATE),
                $this->buildComponentDto(self::COMPONENT_2_NAME, 2, self::NATIONAL_COMPONENT_2_FAIL_RATE),
            ]
        );

        $this->nationalBreakdownDto->getReportStatus()->setIsCompleted(true);

        $this->nationalGroupPerformanceDto = $this->buildMotTestingPerformanceDto(
            self::NATIONAL_VEHICLE_AGE,
            self::NATIONAL_TESTS_FAILED,
            self::NATIONAL_TESTS_DONE,
            self::NATIONAL_TEST_TIME
        );

        $this->vehicleTestingStationDto = $this->buildVehicleTestingStationDto();

        $this->sut = new UserTestQualityCsvMapper(
            $this->userBreakdownDto,
            $this->nationalBreakdownDto,
            $this->nationalGroupPerformanceDto,
            $this->vehicleTestingStationDto,
            self::GROUP,
            self::MONTH,
            self::YEAR
        );

        $this->csvFile = $this->sut->toCsvFile();
    }

    private function buildComponentDto($name, $id, $percentageFailed)
    {
        $componentDto = new ComponentDto();
        $componentDto->setName($name)
            ->setId($id)
            ->setPercentageFailed($percentageFailed);

        return $componentDto;
    }

    private function buildComponentBreakdownDto($displayName, $userName, $components, $groupPerformance)
    {
        $componentBreakdownDto = new ComponentBreakdownDto();
        $componentBreakdownDto->setDisplayName($displayName)
            ->setUserName($userName)
            ->setGroupPerformance($groupPerformance)
            ->setComponents($components);

        return $componentBreakdownDto;
    }

    private function buildMotTestingPerformanceDto($vehicleAge, $percentageFailed, $total, $averageTime)
    {
        $motTestingPerformanceDto = new MotTestingPerformanceDto();
        $motTestingPerformanceDto->setAverageVehicleAgeInMonths($vehicleAge)
            ->setPercentageFailed($percentageFailed)
            ->setTotal($total)
            ->setAverageTime(new TimeSpan(0, 0, $averageTime, 0));

        return $motTestingPerformanceDto;
    }

    private function buildVehicleTestingStationDto()
    {
        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setName(self::SITE_NAME)
            ->setSiteNumber(self::SITE_ID);

        return $vtsDto;
    }

    public function testCsvFileHasCorrectColumnCount()
    {
        $this->assertCount(self::COLUMN_COUNT, $this->csvFile->getHeaders());

        $rows = $this->csvFile->getRows();
        foreach ($rows as $row) {
            $this->assertCount(self::COLUMN_COUNT, $row);
        }
    }

    public function testValuesArePopulatedCorrectly()
    {
        $rows = $this->csvFile->getRows();
        $testerRow = $rows[0];
        $nationalRow = $rows[1];

        $this->assertEquals(self::SITE_NAME, $testerRow[self::SITE_NAME_COLUMN]);
        $this->assertEquals(self::NOT_AVAILABLE, $nationalRow[self::SITE_NAME_COLUMN]);

        $this->assertEquals(self::SITE_ID, $testerRow[self::SITE_ID_COLUMN]);
        $this->assertEquals(self::NOT_AVAILABLE, $nationalRow[self::SITE_ID_COLUMN]);

        $this->assertEquals('Tester', $testerRow[self::TESTER_NATIONAL_COLUMN]);
        $this->assertEquals('National', $nationalRow[self::TESTER_NATIONAL_COLUMN]);

        $this->assertEquals(self::TESTER_NAME, $testerRow[self::TESTER_NAME_COLUMN]);
        $this->assertEquals(self::NOT_AVAILABLE, $nationalRow[self::TESTER_NAME_COLUMN]);

        $this->assertEquals(self::TESTER_USER_ID, $testerRow[self::USER_ID_COLUMN]);
        $this->assertEquals(self::NOT_AVAILABLE, $nationalRow[self::USER_ID_COLUMN]);

        $this->assertEquals(self::GROUP, $testerRow[self::GROUP_COLUMN]);
        $this->assertEquals(self::GROUP, $nationalRow[self::GROUP_COLUMN]);

        $this->assertEquals(self::TESTER_TESTS_DONE, $testerRow[self::TESTS_DONE_COLUMN]);
        $this->assertEquals(self::NATIONAL_TESTS_DONE, $nationalRow[self::TESTS_DONE_COLUMN]);

        $this->assertEquals(self::EXPECTED_TESTER_VEHICLE_AGE, $testerRow[self::VEHICLE_AGE_COLUMN]);
        $this->assertEquals(self::EXPECTED_NATIONAL_VEHICLE_AGE, $nationalRow[self::VEHICLE_AGE_COLUMN]);

        $this->assertEquals(self::TESTER_TEST_TIME, $testerRow[self::TEST_TIME_COLUMN]);
        $this->assertEquals(self::NATIONAL_TEST_TIME, $nationalRow[self::TEST_TIME_COLUMN]);

        $this->assertEquals(self::EXPECTED_TESTER_TESTS_FAILED, $testerRow[self::TESTS_FAILED_COLUMN]);
        $this->assertEquals(self::EXPECTED_NATIONAL_TESTS_FAILED, $nationalRow[self::TESTS_FAILED_COLUMN]);

        $this->assertEquals(self::EXPECTED_TESTER_COMPONENT_1_FAIL_RATE, $testerRow[self::COMPONENT_1_COLUMN]);
        $this->assertEquals(self::EXPECTED_NATIONAL_COMPONENT_1_FAIL_RATE, $nationalRow[self::COMPONENT_1_COLUMN]);

        $this->assertEquals(self::EXPECTED_TESTER_COMPONENT_2_FAIL_RATE, $testerRow[self::COMPONENT_2_COLUMN]);
        $this->assertEquals(self::EXPECTED_NATIONAL_COMPONENT_2_FAIL_RATE, $nationalRow[self::COMPONENT_2_COLUMN]);
    }

    public function testComponentColumnsAreAdded()
    {
        $headers = $this->csvFile->getHeaders();
        $this->assertEquals(self::COMPONENT_1_NAME, $headers[self::COMPONENT_1_COLUMN]);
        $this->assertEquals(self::COMPONENT_2_NAME, $headers[self::COMPONENT_2_COLUMN]);
    }

    public function testFileNameIsCorrect()
    {
        $fileName = $this->csvFile->getFileName();
        $this->assertEquals('Test-quality-information_John-Smith-Tester_tester123_V1234_Group-A_June-2016.csv', $fileName);
    }

    private function buildNationalComponentStatisticsDto($components)
    {
        $dto = new NationalComponentStatisticsDto();
        $dto->setComponents($components);

        return $dto;
    }
}
