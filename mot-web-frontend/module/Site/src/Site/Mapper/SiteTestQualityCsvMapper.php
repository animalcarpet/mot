<?php
namespace Site\Mapper;

use Core\File\CsvFile;
use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;

class SiteTestQualityCsvMapper
{
    const NOT_APPLICABLE = "n/a";
    const NOT_AVAILABLE = "Not Available";

    private $siteGroupPerformance;
    private $nationalGroupPerformance;
    private $vehicleTestingStation;
    private $group;
    private $month;
    private $year;
    private $isNationalDataAvailable;

    public function __construct(
        SiteGroupPerformanceDto $siteGroupPerformance,
        $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalGroupPerformance = null,
        VehicleTestingStationDto $vehicleTestingStation,
        $group,
        $month,
        $year
    )
    {
        $this->siteGroupPerformance = $siteGroupPerformance;
        $this->isNationalDataAvailable = $isNationalDataAvailable;
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

        $csvFile->addRows(
            $this->buildContent(
                $this->siteGroupPerformance,
                $this->nationalGroupPerformance,
                $this->vehicleTestingStation
            )
        );
        $csvFile->setFileName($this->buildFileName());

        return $csvFile;
    }

    private function buildHeader()
    {
        return [
            'Site name',
            'Site ID',
            'Date Period',
            'Site/Tester/National',
            'Group',
            'Tests done',
            'Average vehicle age',
            'Average test time',
            'Tests failed'
        ];
    }

    private function buildContent(
        SiteGroupPerformanceDto $siteGroupPerformance,
        MotTestingPerformanceDto $nationalGroupPerformance = null,
        $vehicleTestingStation
    )
    {
        $content = [];
        $content[] = $this->buildSiteRow($siteGroupPerformance->getTotal(), $vehicleTestingStation);

        $statistics = $siteGroupPerformance->getStatistics();
        if (!empty($statistics)) {
            foreach ($statistics as $employeePerformance) {
                $content[] = $this->buildTesterRow($employeePerformance, $vehicleTestingStation);
            }
        }

        $content[] = $this->buildNationalRow($nationalGroupPerformance);

        return $content;
    }

    private function buildSiteRow(
        MotTestingPerformanceDto $motTestingPerformance,
        VehicleTestingStationDto $vehicleTestingStation
    )
    {
        return [
            $vehicleTestingStation->getName(),
            $vehicleTestingStation->getSiteNumber(),
            $this->getDate(),
            'Site',
            $this->group,
            $motTestingPerformance->getTotal(),
            VehicleAgeFormatter::calculateVehicleAge($motTestingPerformance->getAverageVehicleAgeInMonths()),
            $motTestingPerformance->getAverageTime()->getTotalMinutes(),
            round($motTestingPerformance->getPercentageFailed()) . "%",
        ];
    }

    private function buildNationalRow(MotTestingPerformanceDto $nationalGroupPerformance = null)
    {
        return [
            self::NOT_APPLICABLE,
            self::NOT_APPLICABLE,
            $this->getDate(),
            'National',
            $this->group,
            $this->isNationalDataAvailable ? $nationalGroupPerformance->getTotal() : self::NOT_AVAILABLE,
            $this->isNationalDataAvailable ? VehicleAgeFormatter::calculateVehicleAge($nationalGroupPerformance->getAverageVehicleAgeInMonths()) : self::NOT_AVAILABLE,
            $this->isNationalDataAvailable ? $nationalGroupPerformance->getAverageTime()->getTotalMinutes() : self::NOT_AVAILABLE,
            $this->isNationalDataAvailable ? round($nationalGroupPerformance->getPercentageFailed()) . "%" : self::NOT_AVAILABLE,
        ];
    }

    private function buildTesterRow(
        EmployeePerformanceDto $employeePerformance,
        VehicleTestingStationDto $vehicleTestingStation
    )
    {
        return [
            $vehicleTestingStation->getName(),
            $vehicleTestingStation->getSiteNumber(),
            $this->getDate(),
            $employeePerformance->getUsername(),
            $this->group,
            $employeePerformance->getTotal(),
            VehicleAgeFormatter::calculateVehicleAge($employeePerformance->getAverageVehicleAgeInMonths()),
            $employeePerformance->getAverageTime()->getTotalMinutes(),
            round($employeePerformance->getPercentageFailed()) . "%",
        ];
    }

    private function buildFileName()
    {
        $pattern = "Test-quality-information_%s_%s_Group-%s_%s.csv";

        return sprintf($pattern,
            $this->vehicleTestingStation->getName(),
            $this->vehicleTestingStation->getSiteNumber(),
            $this->group, $this->getDate('F-Y')
        );
    }

    private function getDate($format = 'F Y')
    {
        $date = new \DateTime();
        $date->setDate($this->year, $this->month, 1);

        return $date->format($format);
    }
}