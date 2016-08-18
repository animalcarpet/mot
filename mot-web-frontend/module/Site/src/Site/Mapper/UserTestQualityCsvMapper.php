<?php
namespace Site\Mapper;

use Core\File\CsvFile;
use Core\Formatting\ComponentFailRateFormatter;
use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;

class UserTestQualityCsvMapper
{
    const NOT_APPLICABLE = "n/a";
    const NOT_AVAILABLE = "Not available";

    private $userBreakdown;
    private $nationalBreakdown;
    private $vehicleTestingStation;
    private $group;
    private $month;
    private $year;

    public function __construct(
        ComponentBreakdownDto $userBreakdown,
        NationalComponentStatisticsDto $nationalBreakdown,
        MotTestingPerformanceDto $nationalGroupPerformance = null,
        VehicleTestingStationDto $vehicleTestingStation,
        $group,
        $month,
        $year
    )
    {
        $this->userBreakdown = $userBreakdown;
        $this->nationalBreakdown = $nationalBreakdown;
        $this->nationalGroupPerformance = $nationalGroupPerformance;
        $this->vehicleTestingStation = $vehicleTestingStation;
        $this->group = $group;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @return CsvFile
     */
    public function toCsvFile()
    {
        $csvFile = new CsvFile();
        $csvFile->setHeaders($this->buildHeader());
        $csvFile->addRow($this->buildTesterRow($this->userBreakdown, $this->vehicleTestingStation));
        $csvFile->addRow($this->buildNationalRow($csvFile));
        $csvFile->setFileName($this->buildFileName());

        return $csvFile;
    }

    private function buildHeader()
    {
        $header = [
            'Site name',
            'Site ID',
            'Date Period',
            'Tester/National',
            'Tester name',
            'User ID',
            'Group',
            'Tests done',
            'Average vehicle age',
            'Average test time',
            'Tests failed',
        ];

        $components = $this->userBreakdown->getComponents();

        foreach ($components as $component) {
            $header[] = $component->getName();
        }

        return $header;
    }

    private function buildTesterRow(
        ComponentBreakdownDto $userBreakdown,
        VehicleTestingStationDto $site
    )
    {
        $groupPerformance = $userBreakdown->getGroupPerformance();

        $row = [
            $site->getName(),
            $site->getSiteNumber(),
            $this->getDate(),
            'Tester',
            $userBreakdown->getDisplayName(),
            $userBreakdown->getUserName(),
            $this->group,
            $groupPerformance->getTotal(),
            VehicleAgeFormatter::calculateVehicleAge($groupPerformance->getAverageVehicleAgeInMonths()),
            $groupPerformance->getAverageTime()->getTotalMinutes(),
            round($groupPerformance->getPercentageFailed()) . "%",
        ];

        foreach ($userBreakdown->getComponents() as $component) {
            $row[] = ComponentFailRateFormatter::format($component->getPercentageFailed()) . "%";
        }

        return $row;
    }

    private function buildNationalRow(CsvFile $csv)
    {
        if ($this->nationalBreakdown->getReportStatus()->getIsCompleted()) {
            $row = [
                self::NOT_APPLICABLE,
                self::NOT_APPLICABLE,
                $this->getDate(),
                'National',
                self::NOT_APPLICABLE,
                self::NOT_APPLICABLE,
                $this->group,
                $this->nationalGroupPerformance->getTotal(),
                VehicleAgeFormatter::calculateVehicleAge($this->nationalGroupPerformance->getAverageVehicleAgeInMonths()),
                $this->nationalGroupPerformance->getAverageTime()->getTotalMinutes(),
                round($this->nationalGroupPerformance->getPercentageFailed()) . "%",
            ];

            $components = $this->nationalBreakdown->getComponents();
            foreach ($components as $component) {
                $row[] = ComponentFailRateFormatter::format($component->getPercentageFailed()) . "%";
            }
        } else {
            $row = [
                self::NOT_APPLICABLE,
                self::NOT_APPLICABLE,
                $this->getDate(),
                'National',
                self::NOT_APPLICABLE,
                self::NOT_APPLICABLE,
                $this->group,
                self::NOT_AVAILABLE,
                self::NOT_AVAILABLE,
                self::NOT_AVAILABLE,
                self::NOT_AVAILABLE,
            ];

            $missingRowsCount = $csv->getColumnCount() - count($row);

            for ($i = 0; $i < $missingRowsCount; $i++) {
                $row[] = self::NOT_AVAILABLE;
            }
        }
        return $row;
    }

    private function buildFileName()
    {
        $pattern = "Test-quality-information_%s_%s_%s_Group-%s_%s.csv";

        return str_replace(" ", "-", sprintf($pattern,
                $this->userBreakdown->getDisplayName(),
                $this->userBreakdown->getUserName(),
                $this->vehicleTestingStation->getSiteNumber(),
                $this->group,
                $this->getDate('F-Y')
            )
        );
    }

    private function getDate($format = 'F Y')
    {
        $date = new \DateTime();
        $date->setDate($this->year, $this->month, 1);

        return $date->format($format);
    }
}