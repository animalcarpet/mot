<?php
namespace Site\Csv\TQI;

use Core\Formatting\FailedTestsPercentageFormatter;
use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use Site\Csv\TQI\Enum\InitialPerformanceRows;

class InitialTestPerformanceTqiCsvSection
{
    /** @var EmployeePerformanceDto[] */
    private $employeePerformanceDtos;
    /** @var MotTestingPerformanceDto */
    private $siteAverage;
    /** @var MotTestingPerformanceDto */
    private $nationalAverage;
    /** @var int */
    private $colsNumber;
    /** @var int */
    private $rowsNumber;

    const HEADERS_COLUMN = 0;

    /**
     * InitialTestPerformanceTqiCsvSection constructor.
     * @param EmployeePerformanceDto[] $employeePerformanceDtos
     * @param MotTestingPerformanceDto $siteAverage
     * @param bool $isNationalDataAvailable
     * @param MotTestingPerformanceDto $nationalAverage
     * @param int $colsNumber
     */
    public function __construct(
        array $employeePerformanceDtos,
        MotTestingPerformanceDto $siteAverage,
        bool $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalAverage = null,
        int $colsNumber
    )
    {
        $this->employeePerformanceDtos = $employeePerformanceDtos;
        $this->siteAverage = $siteAverage;
        $this->nationalAverage = $this->getNationalAverage($isNationalDataAvailable, $nationalAverage);
        $this->colsNumber = $colsNumber;
        $this->rowsNumber = 6;
        $this->failedTestsPercentageFormatter = new FailedTestsPercentageFormatter();
    }

    public function build(): array
    {
        $csv = new Csv($this->rowsNumber, $this->colsNumber);
        $csv
            ->addField(InitialPerformanceRows::SECTION_TITLE, self::HEADERS_COLUMN, 'Initial test performance')
            ->addField(InitialPerformanceRows::TESTS_DONE,self::HEADERS_COLUMN, 'Tests done')
            ->addField(InitialPerformanceRows::AVG_VEHICLE_AGE, self::HEADERS_COLUMN, 'Average vehicle age')
            ->addField(InitialPerformanceRows::AVG_TEST_TIME, self::HEADERS_COLUMN, 'Average test time')
            ->addField(InitialPerformanceRows::TESTS_FAILED, self::HEADERS_COLUMN, 'Tests failed');

        $column = 1;
        foreach ($this->employeePerformanceDtos as $employeePerformance) {
            $csv = $this->addPerformanceInfoColumn($employeePerformance, $csv, $column);
            $column++;
        }

        //site average
        $csv = $this->addPerformanceInfoColumn($this->siteAverage, $csv, $column);

        //national average
        $column++;
        $csv = $this->addPerformanceInfoColumn($this->nationalAverage, $csv, $column);

        return $csv->toArray();
    }

    private function addPerformanceInfoColumn(MotTestingPerformanceDto $performanceDto, Csv $csv, int $column)
    {
        return $csv
            ->addField(InitialPerformanceRows::TESTS_DONE, $column, $performanceDto->getTotal())
            ->addField(InitialPerformanceRows::AVG_VEHICLE_AGE, $column, $this->formatAverageVehicleAge($performanceDto))
            ->addField(InitialPerformanceRows::AVG_TEST_TIME, $column, $performanceDto->getAverageTime()->getTotalMinutes())
            ->addField(InitialPerformanceRows::TESTS_FAILED, $column, $this->failedTestsPercentageFormatter->format($performanceDto->getPercentageFailed()));
    }

    private function formatAverageVehicleAge(MotTestingPerformanceDto $testingPerformanceDto) {
        return $testingPerformanceDto->getIsAverageVehicleAgeAvailable()
            ? VehicleAgeFormatter::calculateVehicleAge($testingPerformanceDto->getAverageVehicleAgeInMonths())
            : 0;
    }

    /**
     * Returns the provided or empty $nationalAverage if none is available
     * @param bool $isNationalDataAvailable
     * @param MotTestingPerformanceDto $nationalAverage
     * @return MotTestingPerformanceDto
     */
    private function getNationalAverage(bool $isNationalDataAvailable, MotTestingPerformanceDto $nationalAverage = null)
    {
        return $isNationalDataAvailable && isset($nationalAverage)
            ? $nationalAverage
            : (new MotTestingPerformanceDto())
                ->setAverageTime(new TimeSpan(0, 0, 0, 0));
    }
}
