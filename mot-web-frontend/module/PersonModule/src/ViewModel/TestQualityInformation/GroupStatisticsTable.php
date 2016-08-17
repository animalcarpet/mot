<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation;


use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use Site\ViewModel\TimeSpanFormatter;

class GroupStatisticsTable
{
    const TEXT_NOT_AVAILABLE = 'Not available';

    private $isNationalDataAvailable;
    private $groupCode;
    private $groupDescription;

    private $testCount;
    private $averageTestDuration;
    private $failurePercentage;
    private $averageVehicleAge;

    private $nationalTestCount;
    private $nationalAverageTestDuration;
    private $nationalPercentageFailed;
    private $nationalAverageVehicleAge;
    private $componentLinkText;
    private $componentLink;

    public function __construct(
        EmployeePerformanceDto $groupPerformanceDto = null,
        $isNationalDataAvailable,
        MotTestingPerformanceDto $nationalTestingPerformanceDto = null,
        $groupDescription,
        $groupCode,
        $componentLinkText,
        $componentLink
    )
    {
        $timeSpanFormatter = new TimeSpanFormatter();
        $this->groupCode = $groupCode;
        $this->groupDescription = $groupDescription;
        $this->isNationalDataAvailable = $isNationalDataAvailable;
        $this->componentLinkText = $componentLinkText;
        $this->componentLink = $componentLink;

        if (empty($groupPerformanceDto)) {
            $this->testCount = 0;
            $this->averageTestDuration = self::TEXT_NOT_AVAILABLE;
            $this->failurePercentage = self::TEXT_NOT_AVAILABLE;
            $this->averageVehicleAge = self::TEXT_NOT_AVAILABLE;
        } else {
            $this->testCount = $groupPerformanceDto->getTotal();
            $this->averageTestDuration = $timeSpanFormatter->formatForTestQualityInformationView($groupPerformanceDto->getAverageTime());
            $this->averageVehicleAge = $this->determineVtsGroupAverageVehicleAge($groupPerformanceDto);
            $this->failurePercentage = $groupPerformanceDto->getPercentageFailed();
        }

        if (!empty($nationalTestingPerformanceDto)) {
            $this->nationalTestCount = $nationalTestingPerformanceDto->getTotal();
            if ($this->nationalTestCount > 0) {
                $this->nationalAverageTestDuration = $timeSpanFormatter->formatForTestQualityInformationView($nationalTestingPerformanceDto->getAverageTime());
                $this->nationalAverageVehicleAge = $this->determineVtsGroupAverageVehicleAge($nationalTestingPerformanceDto);
                $this->nationalPercentageFailed = $nationalTestingPerformanceDto->getPercentageFailed();
            }
        }
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
        return $this->testCount > 0;
    }

    public function getNotAvailableText()
    {
        return self::TEXT_NOT_AVAILABLE;
    }

    public function getTestCount()
    {
        return $this->testCount;
    }

    public function getAverageVehicleAge()
    {
        return $this->averageVehicleAge;
    }

    public function getAverageTestDuration()
    {
        return $this->averageTestDuration;
    }

    public function getFailurePercentage()
    {
        return $this->convertPercentFailed($this->failurePercentage);
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

    public function getComponentLinkText()
    {
        return sprintf($this->componentLinkText, $this->getGroupCode());
    }

    private function convertPercentFailed($value)
    {
        if (is_numeric($value)) {
            return number_format($value, 0) . '%';
        } else {
            return $value;
        }
    }

    public function getComponentLink()
    {
        return $this->componentLink;
    }
}