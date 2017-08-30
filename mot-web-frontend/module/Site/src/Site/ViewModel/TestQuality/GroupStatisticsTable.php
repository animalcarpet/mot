<?php

namespace Site\ViewModel\TestQuality;

use Core\Formatting\VehicleAgeFormatter;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\AverageGroupStatisticsHeader;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Formatting\PersonFullNameFormatter;
use DvsaCommon\Utility\TypeCheck;
use Site\ViewModel\TimeSpanFormatter;

class GroupStatisticsTable
{
    const NATIONAL_AVERAGE = 'National average';
    private $testCount;
    /** @var TestQualityStatisticRow[] */
    private $testerRows;
    private $timeSpanFormatter;
    private $personFullNameFormatter;

    /** @var TestQualityStatisticRow */
    private $nationalStatistic;

    private $groupPerformanceDto;
    private $nationalTestingPerformanceDto;
    /** @var VehicleTestingStationDto */
    private $site;
    private $isNationalDataAvailable;
    /** @var AverageGroupStatisticsHeader */
    private $averageGroupStatisticsHeader;
    private $groupCode;

    public function __construct(
        SiteGroupPerformanceDto $groupPerformanceDto,
        $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalTestingPerformanceDto = null,
        $groupName,
        $groupDescription,
        $groupCode,
        $site
    ) {
        $this->personFullNameFormatter = new PersonFullNameFormatter();
        $this->timeSpanFormatter = new TimeSpanFormatter();
        $this->site = $site;
        $this->groupCode = $groupCode;
        $this->averageGroupStatisticsHeader = new AverageGroupStatisticsHeader();
        $this->averageGroupStatisticsHeader->setGroupCode($groupCode);
        $this->averageGroupStatisticsHeader->setGroupDescription($groupDescription);
        $this->isNationalDataAvailable = $isNationalDataAvailable;

        $testers = $groupPerformanceDto->getStatistics();

        if (empty($testers)) {
            $this->testCount = 0;
            $this->averageGroupStatisticsHeader->setIsAverageVehicleAgeAvailable(false);
            $this->averageGroupStatisticsHeader->setFailurePercentage(0);
        } else {
            $this->testCount = $groupPerformanceDto->getTotal()->getTotal();
            $this->averageGroupStatisticsHeader->setIsAverageVehicleAgeAvailable($groupPerformanceDto->getTotal()->getIsAverageVehicleAgeAvailable());
            $this->averageGroupStatisticsHeader->setAverageTestDuration($this->timeSpanFormatter->formatForTestQualityInformationView($groupPerformanceDto->getTotal()->getAverageTime()));
            $this->averageGroupStatisticsHeader->setAverageVehicleAge($this->determineVtsGroupAverageVehicleAge($groupPerformanceDto->getTotal()));
            $this->averageGroupStatisticsHeader->setFailurePercentage($groupPerformanceDto->getTotal()->getPercentageFailed());
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

        $this->testerRows = $this->createTesterRows($groupPerformanceDto->getStatistics());
        $this->groupPerformanceDto = $groupPerformanceDto;
        $this->nationalTestingPerformanceDto = $nationalTestingPerformanceDto;

        $this->averageGroupStatisticsHeader->setHasTests($this->hasTests());
        $this->averageGroupStatisticsHeader->setTestCount($this->getTestCount());
    }

    /**
     * @return boolean
     */
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
     * @param $testers EmployeePerformanceDto[]
     *
     * @return MotTestingPerformanceDto
     *
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
                ->setFullName($this->personFullNameFormatter
                    ->format($tester->getFirstName(), $tester->getMiddleName(), $tester->getFamilyName()))
                ->setGroupCode($this->getGroupCode())
                ->setSiteId($this->site->getId())
                ->setTestCount($tester->getTotal())
                ->setAverageTestDuration($this->timeSpanFormatter->formatForTestQualityInformationView($tester->getAverageTime()))
                ->setAverageVehicleAge(VehicleAgeFormatter::calculateVehicleAge($tester->getAverageVehicleAgeInMonths()))
                ->setFailurePercentage($tester->getPercentageFailed());
        }

        return $rows;
    }

    /**
     * @param MotTestingPerformanceDto $groupPerformanceDto
     *
     * @return int
     */
    private function determineVtsGroupAverageVehicleAge(MotTestingPerformanceDto $groupPerformanceDto)
    {
        $average = 0;

        if ($groupPerformanceDto->getIsAverageVehicleAgeAvailable()) {
            $average = VehicleAgeFormatter::calculateVehicleAge(
                $groupPerformanceDto->getAverageVehicleAgeInMonths()
            );
        }

        return $average;
    }

    /**
     * @param MotTestingPerformanceDto $nationalPerformanceDto
     *
     * @return string
     */
    protected function determineNationalAverageVehicleAge(MotTestingPerformanceDto $nationalPerformanceDto)
    {
        $text = '';
        if ($nationalPerformanceDto->getTotal() >= 1 && $nationalPerformanceDto->getIsAverageVehicleAgeAvailable()) {
            $text = VehicleAgeFormatter::calculateVehicleAge($nationalPerformanceDto->getAverageVehicleAgeInMonths());
        }

        return $text;
    }

    public function isNationalDataAvailable()
    {
        return $this->isNationalDataAvailable;
    }

    /**
     * @return AverageGroupStatisticsHeader
     */
    public function getAverageGroupStatisticsHeader()
    {
        return $this->averageGroupStatisticsHeader;
    }

    public function getGroupCode()
    {
        return $this->groupCode;
    }
}
