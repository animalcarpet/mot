<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation;

use Core\Formatting\VehicleAgeFormatter;
use Dvsa\Mot\Frontend\TestQualityInformation\ViewModel\AverageGroupStatisticsHeader;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Utility\TypeCheck;
use Site\ViewModel\TimeSpanFormatter;

class GroupStatisticsTable
{
    private $isNationalDataAvailable;
    private $groupCode;
    private $groupDescription;

    private $nationalTestCount;
    private $nationalAverageTestDuration;
    private $nationalPercentageFailed;
    private $nationalAverageVehicleAge;

    /** @var AverageGroupStatisticsHeader */
    private $averageGroupStatisticsHeader;

    private $siteTests;
    private $userId;

    /** @var TimeSpanFormatter */
    private $timeSpanFormatter;

    /**
     * GroupStatisticsTable constructor.
     *
     * @param EmployeePerformanceDto|null $groupPerformanceDto
     * @param SiteRowViewModel[]          $siteTests
     * @param bool $isNationalDataAvailable
     * @param MotTestingPerformanceDto|null $nationalTestingPerformanceDto
     * @param $groupDescription
     * @param $groupCode
     * @param int $userId
     */
    public function __construct(
        EmployeePerformanceDto $groupPerformanceDto = null,
        array $siteTests,
        bool $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalTestingPerformanceDto = null,
        $groupDescription,
        $groupCode,
        int $userId
    ) {
        TypeCheck::assertCollectionOfClass($siteTests, SiteRowViewModel::class);

        $this->timeSpanFormatter = new TimeSpanFormatter();
        $this->groupCode = $groupCode;
        $this->groupDescription = $groupDescription;
        $this->isNationalDataAvailable = $isNationalDataAvailable;
        $this->userId = $userId;

        $this->buildAverageGroupStatisticsHeader($groupCode, $groupDescription, $groupPerformanceDto);

        if (!empty($nationalTestingPerformanceDto)) {
            $this->nationalTestCount = $nationalTestingPerformanceDto->getTotal();
            if ($this->nationalTestCount > 0) {
                $this->nationalAverageTestDuration = $this->timeSpanFormatter->formatForTestQualityInformationView($nationalTestingPerformanceDto->getAverageTime());
                $this->nationalAverageVehicleAge = $this->determineVtsGroupAverageVehicleAge($nationalTestingPerformanceDto);
                $this->nationalPercentageFailed = $nationalTestingPerformanceDto->getPercentageFailed();
            }
        }
        $this->siteTests = $siteTests;
    }

    private function buildAverageGroupStatisticsHeader(string $groupCode, string $groupDescription, EmployeePerformanceDto $groupPerformanceDto = null)
    {
        $this->averageGroupStatisticsHeader = new AverageGroupStatisticsHeader();
        $this->averageGroupStatisticsHeader->setHasTests(!empty($groupPerformanceDto));
        $this->averageGroupStatisticsHeader->setGroupDescription($groupDescription);
        $this->averageGroupStatisticsHeader->setGroupCode($groupCode);
        if (empty($groupPerformanceDto)) {
            $this->averageGroupStatisticsHeader->setTestCount(0);
            $this->averageGroupStatisticsHeader->setIsAverageVehicleAgeAvailable(false);
            $this->averageGroupStatisticsHeader->setFailurePercentage(0);
        } else {
            $this->averageGroupStatisticsHeader->setTestCount($groupPerformanceDto->getTotal());
            $this->averageGroupStatisticsHeader->setIsAverageVehicleAgeAvailable($groupPerformanceDto->getIsAverageVehicleAgeAvailable());
            $this->averageGroupStatisticsHeader->setAverageVehicleAge($this->determineVtsGroupAverageVehicleAge($groupPerformanceDto));
            $this->averageGroupStatisticsHeader->setAverageTestDuration(
                $this->timeSpanFormatter->formatForTestQualityInformationView($groupPerformanceDto->getAverageTime())
            );
            $this->averageGroupStatisticsHeader->setFailurePercentage($groupPerformanceDto->getPercentageFailed());
        }
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

    public function getSiteTests()
    {
        return $this->siteTests;
    }

    public function getGroupCode()
    {
        return $this->groupCode;
    }

    public function getGroupDescription()
    {
        return $this->groupDescription;
    }

    public function hasTests()
    {
        return count($this->siteTests) > 0;
    }

    public function isNationalDataAvailable()
    {
        return $this->isNationalDataAvailable;
    }

    public function getNationalTestCount()
    {
        return $this->nationalTestCount;
    }

    public function getNationalAverageTestDuration()
    {
        return $this->nationalAverageTestDuration;
    }

    public function getNationalPercentageFailed()
    {
        return $this->convertPercentFailed($this->nationalPercentageFailed);
    }

    public function getNationalAverageVehicleAge()
    {
        return $this->nationalAverageVehicleAge;
    }

    private function convertPercentFailed($value)
    {
        if (is_numeric($value)) {
            return number_format($value, 0).'%';
        } else {
            return $value;
        }
    }

    /**
     * @return AverageGroupStatisticsHeader
     */
    public function getAverageGroupStatisticsHeader():AverageGroupStatisticsHeader
    {
        return $this->averageGroupStatisticsHeader;
    }

    /**
     * @return int
     */
    public function getUserId():int
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function getIsNationalDataAvailable():bool
    {
        return $this->isNationalDataAvailable && $this->getNationalTestCount() > 0;
    }

}
