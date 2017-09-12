<?php

namespace SiteTest\Csv\TQI;

use DvsaCommon\ApiClient\Statistics\Common\ReportStatusDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\Date\TestDateTimeHolder;
use Site\Csv\TQI\SiteTestQualityCsvBuilder;
use Site\Csv\TQI\TableHeadersTqiCsvSection;

class SiteTestQualityCsvBuilderTest extends \PHPUnit_Framework_TestCase
{
    const VTS_NAME = 'SITE-NAME';
    const VTS_SITE_NUMBER = 'SITE-NUMBER';

    /** @var SiteTestQualityCsvBuilder */
    private $sut;
    /** @var int */
    private $numberOfMonths;
    /** @var string */
    private $vehicleClassGroup;
    /** @var  LastMonthsDateRange */
    private $monthRange;
    /** @var bool */
    private $isNationalDataAvailable;

    public function setUp()
    {
        $this->numberOfMonths = LastMonthsDateRange::THREE_MONTHS;
        $this->vehicleClassGroup = VehicleClassGroupCode::CARS_ETC;
        $this->monthRange = $this->createLastMonthDateRange();
        $this->isNationalDataAvailable = true;

        $this->sut = $this->createSiteTestQualityCsvBuilder();
    }

    private function createSiteTestQualityCsvBuilder()
    {
        return new SiteTestQualityCsvBuilder(
            $this->getSiteGroupPerformance(),
            $this->getComponentBreakdownForTesters(),
            $this->getSiteComponentStatistics(),
            $this->getNationalComponentStatistics(),
            $this->isNationalDataAvailable,
            $this->getNationalGroupPerformance(),
            $this->getVehicleTestingStation(),
            $this->vehicleClassGroup,
            $this->createLastMonthDateRange()
        );
    }

    public function testCsvFileHasCorrectColumnCount()
    {
        $csvFile = $this->sut->toCsvFile();

        $this->assertEquals(5, $csvFile->getColumnCount());

        $rows = $csvFile->getRows();
        foreach ($rows as $row) {
            $this->assertCount(5, $row);
        }
    }

    /**
     * @param string $group
     * @param string $numberOfMonths
     * @dataProvider getFilenameProperties
     */
    public function testFileNameIsCorrect(string $group, string $numberOfMonths)
    {
        $this->vehicleClassGroup = $group;
        $this->numberOfMonths = $numberOfMonths;
        $this->monthRange = $this->createLastMonthDateRange();

        $csvFile = $this->createSiteTestQualityCsvBuilder()->toCsvFile();
        $csvFile->getFileName();

        $site = $this->getVehicleTestingStation();
        $expectedFileName = sprintf(
            SiteTestQualityCsvBuilder::FILE_NAME_PATTERN,
            $site->getName(),
            $site->getSiteNumber(),
            $this->vehicleClassGroup,
            $this->monthRange->getNumberOfMonths()
        );

        $this->assertEquals($expectedFileName, $csvFile->getFileName());
    }

    public function getFilenameProperties(): array
    {
        return [
            [
                VehicleClassGroupCode::BIKES,
                LastMonthsDateRange::ONE_MONTH
            ],
            [
                VehicleClassGroupCode::BIKES,
                LastMonthsDateRange::THREE_MONTHS
            ],
            [
                VehicleClassGroupCode::CARS_ETC,
                LastMonthsDateRange::ONE_MONTH
            ],
            [
                VehicleClassGroupCode::CARS_ETC,
                LastMonthsDateRange::THREE_MONTHS
            ]
        ];
    }


    private function getSiteGroupPerformance(): SiteGroupPerformanceDto
    {
        $motTestingPerformanceDto = new MotTestingPerformanceDto();
        $motTestingPerformanceDto
            ->setTotal(2)
            ->setPercentageFailed(38.23)
            ->setAverageTime(new TimeSpan(0, 0, 58, 0))
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(96)
            ;

        $johnPerformanceDto = new EmployeePerformanceDto();
        $johnPerformanceDto
            ->setFirstName("John")
            ->setFamilyName("Smith")
            ->setPersonId(105)
            ->setUsername("tester105")
            ->setTotal(25)
            ->setPercentageFailed(23.11)
            ->setAverageTime(new TimeSpan(0, 0, 34, 54))
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(145)
            ;

        $brucePerformanceDto = new EmployeePerformanceDto();
        $brucePerformanceDto
            ->setFirstName("Bruce")
            ->setFamilyName("Wayne")
            ->setPersonId(106)
            ->setUsername("batman")
            ->setTotal(178)
            ->setPercentageFailed(98)
            ->setAverageTime(new TimeSpan(0, 2, 22, 44))
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(56)
        ;

        $siteGroupPerformanceDto = new SiteGroupPerformanceDto();
        $siteGroupPerformanceDto->setTotal($motTestingPerformanceDto);
        $siteGroupPerformanceDto->setStatistics([$johnPerformanceDto, $brucePerformanceDto]);

        return $siteGroupPerformanceDto;
    }

    private function getComponentBreakdownForTesters(): array
    {
        $johnMotTestingPerformanceDto = new MotTestingPerformanceDto();
        $johnMotTestingPerformanceDto
            ->setTotal(2)
            ->setPercentageFailed(38.23)
            ->setAverageTime(new TimeSpan(0, 0, 58, 0))
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(96)
        ;

        $johnComponentBreakdownDto = new ComponentBreakdownDto();
        $johnComponentBreakdownDto
            ->setSiteName("Garage")
            ->setDisplayName("John Smith")
            ->setUserName("john_smith")
            ->setGroupPerformance($johnMotTestingPerformanceDto)
            ->setComponents([$this->getWheelComponent(12.45), $this->getEngineComponent(2.97)]);

        $bruceMotTestingPerformanceDto = new MotTestingPerformanceDto();
        $bruceMotTestingPerformanceDto
            ->setTotal(2)
            ->setPercentageFailed(38.23)
            ->setAverageTime(new TimeSpan(0, 0, 58, 0))
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(96)
        ;

        $bruceComponentBreakdownDto = new ComponentBreakdownDto();
        $bruceComponentBreakdownDto
            ->setSiteName("Bat Cave")
            ->setDisplayName("Bruce Wayne")
            ->setUserName("batman")
            ->setGroupPerformance($bruceMotTestingPerformanceDto)
            ->setComponents([$this->getWheelComponent(55.32), $this->getEngineComponent(0.42)]);

        return [$johnComponentBreakdownDto, $bruceComponentBreakdownDto];
    }

    private function getWheelComponent(float $percentageFailed)
    {
        return $this->getComponent(1, $percentageFailed, "wheel");
    }

    private function getEngineComponent(float $percentageFailed)
    {
        return $this->getComponent(1, $percentageFailed, "engine");
    }

    private function getComponent(int $id, float $percentageFailed, string $name): ComponentDto
    {
        $wheelComponentDto = new ComponentDto();
        $wheelComponentDto->setId($id);
        $wheelComponentDto->setPercentageFailed($percentageFailed);
        $wheelComponentDto->setName($name);

        return $wheelComponentDto;
    }

    private function getSiteComponentStatistics(): SiteComponentStatisticsDto
    {
        $siteComponentStatisticsDto = new SiteComponentStatisticsDto();
        $siteComponentStatisticsDto
            ->setSiteId(1)
            ->setGroup($this->vehicleClassGroup)
            ->setMonthRange($this->numberOfMonths)
            ->setComponents([$this->getWheelComponent(65.32), $this->getEngineComponent(45.472)])
            ;

        return $siteComponentStatisticsDto;
    }

    private function getNationalComponentStatistics(): NationalComponentStatisticsDto
    {
        $reportStatusDto = new ReportStatusDto();
        $reportStatusDto->setIsCompleted(true);

        $nationalComponentStatisticsDto = new NationalComponentStatisticsDto();
        $nationalComponentStatisticsDto
            ->setGroup($this->vehicleClassGroup)
            ->setMonthRange($this->numberOfMonths)
            ->setReportStatus($reportStatusDto)
            ->setComponents([$this->getWheelComponent(7.21), $this->getEngineComponent(18.42)]);

        return $nationalComponentStatisticsDto;
    }

    private function getNationalGroupPerformance(): MotTestingPerformanceDto
    {
        $motTestingPerformanceDto = new MotTestingPerformanceDto();
        $motTestingPerformanceDto
            ->setTotal(2679)
            ->setPercentageFailed(27.23)
            ->setAverageTime(new TimeSpan(0, 0, 48, 0))
            ->setIsAverageVehicleAgeAvailable(true)
            ->setAverageVehicleAgeInMonths(105)
        ;

        return $motTestingPerformanceDto;
    }

    private function getVehicleTestingStation(): VehicleTestingStationDto
    {
        $dto = new VehicleTestingStationDto();
        $dto->setName(self::VTS_NAME)
            ->setSiteNumber(self::VTS_SITE_NUMBER);

        return $dto;
    }

    private function createLastMonthDateRange(): LastMonthsDateRange
    {
        $monthRange = new LastMonthsDateRange(new TestDateTimeHolder(new \DateTime('2015-2-15')));
        $monthRange->setNumberOfMonths($this->numberOfMonths);

        return $monthRange;
    }
}
