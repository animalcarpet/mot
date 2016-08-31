<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryResult;

abstract class AbstractTesterPerformanceResult
{
    protected $vehicleClassGroup;
    protected $totalTime;
    protected $failedCount;
    protected $totalCount;
    protected $averageVehicleAgeInMonths;
    protected $isAverageVehicleAgeAvailable;

    public function getVehicleClassGroup()
    {
        return $this->vehicleClassGroup;
    }

    public function setVehicleClassGroup($vehicleClassGroup)
    {
        $this->vehicleClassGroup = $vehicleClassGroup;
        return $this;
    }

    public function getTotalTime()
    {
        return $this->totalTime;
    }

    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
        return $this;
    }

    public function getFailedCount()
    {
        return $this->failedCount;
    }

    public function setFailedCount($failedCount)
    {
        $this->failedCount = $failedCount;
        return $this;
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    public function getAverageVehicleAgeInMonths()
    {
        return $this->averageVehicleAgeInMonths;
    }

    public function setAverageVehicleAgeInMonths($averageVehicleAgeInMonths)
    {
        $this->averageVehicleAgeInMonths = $averageVehicleAgeInMonths;
        return $this;
    }

    public function getIsAverageVehicleAgeAvailable()
    {
        return $this->isAverageVehicleAgeAvailable;
    }

    public function setIsAverageVehicleAgeAvailable($isAverageVehicleAgeAvailable)
    {
        $this->isAverageVehicleAgeAvailable = $isAverageVehicleAgeAvailable;
        return $this;
    }
}