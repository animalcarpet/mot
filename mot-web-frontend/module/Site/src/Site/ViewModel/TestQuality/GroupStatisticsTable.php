<?php
namespace Site\ViewModel\TestQuality;

use Core\Formatting\VehicleAgeFormatter;
use DateTime;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Utility\TypeCheck;
use Site\ViewModel\TimeSpanFormatter;

class GroupStatisticsTable
{
    const NATIONAL_AVERAGE = 'National average';
    private $testCount;
    private $averageTestDuration;
    private $failurePercentage;
    const TEXT_NOT_AVAILABLE = 'Not available';
    /** @var  TestQualityStatisticRow[] */
    private $testerRows;
    private $groupCode;
    private $groupDescription;
    private $averageVehicleAge;

    /** @var DateTime */
    private $viewedDate;

    /** @var TestQualityStatisticRow */
    private $nationalStatistic;

    private $groupPerformanceDto;
    private $nationalTestingPerformanceDto;
    /** @var  VehicleTestingStationDto */
    private $site;
    /** @var  int */
    private $csvFileSize;
    private $isNationalDataAvailable;

    public function __construct(
        SiteGroupPerformanceDto $groupPerformanceDto,
        $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalTestingPerformanceDto = null,
        $groupName,
        $groupDescription,
        $groupCode,
        $site,
        DateTime $viewedDate,
        $csvFileSize
    )
    {
        $this->timeSpanFormatter = new TimeSpanFormatter();
        $this->site = $site;
        $this->groupCode = $groupCode;
        $this->groupDescription = $groupDescription;
        $this->isNationalDataAvailable = $isNationalDataAvailable;

        $testers = $groupPerformanceDto->getStatistics();

        if (empty($testers)) {
            $this->testCount = 0;
            $this->averageTestDuration = self::TEXT_NOT_AVAILABLE;
            $this->failurePercentage = self::TEXT_NOT_AVAILABLE;
            $this->averageVehicleAge = self::TEXT_NOT_AVAILABLE;
        } else {
            $this->testCount = $groupPerformanceDto->getTotal()->getTotal();
            $this->averageTestDuration = $this->timeSpanFormatter->formatForTestQualityInformationView($groupPerformanceDto->getTotal()->getAverageTime());
            $this->averageVehicleAge = $this->determineVtsGroupAverageVehicleAge($groupPerformanceDto->getTotal());
            $this->failurePercentage = $groupPerformanceDto->getTotal()->getPercentageFailed();
        }

        if ($this->isNationalDataAvailable) {
            $this->nationalStatistic = (new TestQualityStatisticRow())
                ->setName(self::NATIONAL_AVERAGE)
                ->setTestCount($nationalTestingPerformanceDto->getTotal())
                ->setFailurePercentage($nationalTestingPerformanceDto->getPercentageFailed())
                ->setAverageVehicleAge($this->determineNationalAverageVehicleAge($nationalTestingPerformanceDto))
                ->setAverageTestDuration($nationalTestingPerformanceDto->getTotal() > 0
                    ? $this->timeSpanFormatter->formatForTestQualityInformationView($nationalTestingPerformanceDto->getAverageTime())
                    : '');
        }
        $this->viewedDate = $viewedDate;

        $this->testerRows = $this->createTesterRows($groupPerformanceDto->getStatistics());
        $this->groupPerformanceDto = $groupPerformanceDto;
        $this->nationalTestingPerformanceDto = $nationalTestingPerformanceDto;
        $this->csvFileSize = $csvFileSize;
    }

    public function hasTests()
    {
        return count($this->testerRows) > 0;
    }

    /**
     * @return TestQualityStatisticRow
     */
    public function getNationalStatistic()
    {
        return $this->nationalStatistic;
    }

    public function getTestCount()
    {
        return $this->testCount;
    }

    public function getAverageTestDuration()
    {
        return $this->averageTestDuration;
    }

    public function getFailurePercentage()
    {
        if (is_numeric($this->failurePercentage)) {
            return number_format($this->failurePercentage, 0) . '%';
        } else {
            return $this->failurePercentage;
        }
    }

    public function getTesterRows()
    {
        return $this->testerRows;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->site->getId();
    }

    /**
     * @return DateTime
     */
    public function getViewedDate()
    {
        return $this->viewedDate;
    }

    /**
     * @param $testers EmployeePerformanceDto[]
     * @return MotTestingPerformanceDto
     * @internal param MotTestingPerformanceDto $national
     */
    private function createTesterRows($testers)
    {
        TypeCheck::assertCollectionOfClass($testers, EmployeePerformanceDto::class);

        /** @var MotTestingPerformanceDto $rows */
        $rows = [];

        foreach ($testers as $tester) {
            $rows[] = (new TestQualityStatisticRow())
                ->setName($tester->getUsername())
                ->setPersonId($tester->getPersonId())
                ->setGroupCode($this->groupCode)
                ->setSiteId($this->site->getId())
                ->setTestCount($tester->getTotal())
                ->setAverageTestDuration($this->timeSpanFormatter->formatForTestQualityInformationView($tester->getAverageTime()))
                ->setAverageVehicleAge($tester->getIsAverageVehicleAgeAvailable()
                    ? VehicleAgeFormatter::calculateVehicleAge($tester->getAverageVehicleAgeInMonths())
                    : self::TEXT_NOT_AVAILABLE
                )
                ->setFailurePercentage($tester->getPercentageFailed())
                ->setViewedDate($this->getViewedDate());
        }

        return $rows;
    }

    public function getGroupCode()
    {
        return $this->groupCode;
    }

    public function getGroupDescription()
    {
        return $this->groupDescription;
    }

    public function getAverageVehicleAge()
    {
        return $this->averageVehicleAge;
    }

    public function getNotAvailableText()
    {
        return self::TEXT_NOT_AVAILABLE;
    }

    /**
     * @param MotTestingPerformanceDto $groupPerformanceDto
     * @return int
     */
    private function determineVtsGroupAverageVehicleAge(MotTestingPerformanceDto $groupPerformanceDto)
    {
        $average = self::TEXT_NOT_AVAILABLE;

        if ($groupPerformanceDto->getIsAverageVehicleAgeAvailable()) {
            $average = VehicleAgeFormatter::calculateVehicleAge(
                $groupPerformanceDto->getAverageVehicleAgeInMonths()
            );
        }

        return $average;
    }

    /**
     * @param MotTestingPerformanceDto $nationalPerformanceDto
     * @return string
     */
    protected function determineNationalAverageVehicleAge(MotTestingPerformanceDto $nationalPerformanceDto)
    {
        $text = self::TEXT_NOT_AVAILABLE;

        if ($nationalPerformanceDto->getTotal() < 1) {
            $text = '';
        } else {
            if ($nationalPerformanceDto->getIsAverageVehicleAgeAvailable()) {
                $text = VehicleAgeFormatter::calculateVehicleAge($nationalPerformanceDto->getAverageVehicleAgeInMonths());
            }
        }

        return $text;
    }

    public function getCsvFileSize()
    {
        $kb = $this->csvFileSize / 1024;
        if ($kb > 1) {
            return round($kb) . 'KB';
        } else {
            return '1KB';
        }
    }

    public function getMonth()
    {
        return (int)$this->viewedDate->format('m');
    }

    public function getYear()
    {
        return (int)$this->viewedDate->format('Y');
    }

    public function isNationalDataAvailable()
    {
        return $this->isNationalDataAvailable;
    }
}