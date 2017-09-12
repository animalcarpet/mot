<?php

namespace Site\Csv\TQI;

use Core\File\CsvFile;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;

class SiteTestQualityCsvBuilder
{
    const FILE_NAME_PATTERN = "Test-quality-information_%s_%s_Group-%s_%s_month(s).csv";

    private $siteGroupPerformance;
    private $nationalGroupPerformance;
    private $vehicleTestingStation;
    private $group;
    private $monthRange;
    private $isNationalDataAvailable;
    private $componentBreakdownForTesters;
    private $siteComponentStatisticsDto;
    private $nationalComponentStatisticsDto;

    /**
     * SiteTestQualityCsvMapper constructor.
     * @param SiteGroupPerformanceDto $siteGroupPerformance
     * @param ComponentBreakdownDto[] $componentBreakdownForTesters
     * @param SiteComponentStatisticsDto $siteComponentStatisticsDto
     * @param NationalComponentStatisticsDto $nationalComponentStatisticsDto
     * @param MotTestingPerformanceDto|null $nationalGroupPerformance
     * @param VehicleTestingStationDto $vehicleTestingStation
     * @param string $group
     * @param LastMonthsDateRange $monthRange
     */
    public function __construct(
        SiteGroupPerformanceDto $siteGroupPerformance,
        array $componentBreakdownForTesters = [],
        SiteComponentStatisticsDto $siteComponentStatisticsDto,
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalGroupPerformance = null,
        VehicleTestingStationDto $vehicleTestingStation,
        string $group,
        LastMonthsDateRange $monthRange
    ) {
        $this->siteGroupPerformance = $siteGroupPerformance;
        $this->componentBreakdownForTesters = $componentBreakdownForTesters;
        $this->siteComponentStatisticsDto = $siteComponentStatisticsDto;
        $this->nationalComponentStatisticsDto = $nationalComponentStatisticsDto;
        $this->isNationalDataAvailable = $isNationalDataAvailable;
        $this->nationalGroupPerformance = $nationalGroupPerformance;
        $this->vehicleTestingStation = $vehicleTestingStation;
        $this->group = $group;
        $this->monthRange = $monthRange;
    }

    /**
     * @return CsvFile
     */
    public function toCsvFile()
    {
        $csvFile = new CsvFile();

        $csvFile->addRows(
            $this->buildContent()
        );
        $csvFile->setFileName($this->buildFileName());

        return $csvFile;
    }

    private function buildContent(): array
    {
        $colsNumber = count($this->componentBreakdownForTesters) + 3;;
        $statistics = $this->siteGroupPerformance->getStatistics();

        $topTqiCsvSection = $this->createTopSection($colsNumber);
        $tableHeadersCsvSection = new TableHeadersTqiCsvSection($statistics, $colsNumber);
        $initialTestPerformanceTqiCsvSection = $this->createInitialPerformanceSection($statistics, $colsNumber);
        $failuresByCategoryTqiCsvSection = $this->createFailuresByCategorySection($colsNumber);

        $csv = array_merge(
            $topTqiCsvSection->build(),
            $tableHeadersCsvSection->build(),
            $initialTestPerformanceTqiCsvSection->build(),
            $failuresByCategoryTqiCsvSection->build()
        );

        return $csv;
    }

    private function buildFileName(): string
    {
        return sprintf(self::FILE_NAME_PATTERN,
            $this->vehicleTestingStation->getName(),
            $this->vehicleTestingStation->getSiteNumber(),
            $this->group,
            $this->monthRange->getNumberOfMonths()
        );
    }

    private function createTopSection($colsNumber)
    {
        return new TopTqiCsvSection(
            $this->vehicleTestingStation,
            $this->monthRange,
            $this->group,
            $colsNumber
        );
    }

    /**
     * @param EmployeePerformanceDto[] $statistics
     * @param $colsNumber
     * @return InitialTestPerformanceTqiCsvSection
     */
    private function createInitialPerformanceSection(array $statistics, int $colsNumber)
    {
        return new InitialTestPerformanceTqiCsvSection(
            $statistics,
            $this->siteGroupPerformance->getTotal(),
            $this->isNationalDataAvailable,
            $this->nationalGroupPerformance,
            $colsNumber
        );

    }

    private function createFailuresByCategorySection(int $colsNumber)
    {
        return new FailuresByCategoryTqiCsvSection(
            $this->nationalComponentStatisticsDto,
            $this->siteComponentStatisticsDto,
            $this->componentBreakdownForTesters,
            $colsNumber
        );
    }
}
