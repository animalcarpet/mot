<?php

namespace Dvsa\Mot\Frontend\TestQualityInformation\ViewModel;

class AverageGroupStatisticsHeader
{
    private $groupCode;
    private $groupDescription;
    private $testCount;
    /** @var bool */
    private $hasTests;
    /** @var bool */
    private $isAverageVehicleAgeAvailable;
    private $averageVehicleAge;
    /** @var string|int */
    private $averageTestDuration;
    /** @var string|int */
    private $failurePercentage;

    /**
     * @param int|string $failurePercentage
     */
    public function setFailurePercentage($failurePercentage)
    {
        $this->failurePercentage = $failurePercentage;
    }


    /**
     * @return string
     */
    public function getFailurePercentage():string
    {
        if (is_numeric($this->failurePercentage)) {
            return number_format($this->failurePercentage, 0).'%';
        } else {
            return $this->failurePercentage;
        }
    }

    /**
     * @return int|string
     */
    public function getAverageTestDuration()
    {
        return $this->averageTestDuration;
    }

    /**
     * @param int|string $averageTestDuration
     */
    public function setAverageTestDuration($averageTestDuration)
    {
        $this->averageTestDuration = $averageTestDuration;
    }

    public function getAverageVehicleAge()
    {
        return $this->averageVehicleAge;
    }

    public function setAverageVehicleAge($averageVehicleAge)
    {
        $this->averageVehicleAge = $averageVehicleAge;
    }

    /**
     * @return bool
     */
    public function getIsAverageVehicleAgeAvailable()
    {
        return $this->isAverageVehicleAgeAvailable;
    }

    /**
     * @param bool $isAverageVehicleAgeAvailable
     */
    public function setIsAverageVehicleAgeAvailable($isAverageVehicleAgeAvailable)
    {
        $this->isAverageVehicleAgeAvailable = $isAverageVehicleAgeAvailable;
    }

    /**
     * @return bool
     */
    public function hasTests()
    {
        return $this->hasTests;
    }

    /**
     * @param bool $hasTests
     */
    public function setHasTests($hasTests)
    {
        $this->hasTests = $hasTests;
    }

    public function isAverageTestTime()
    {
        return $this->getTestCount() > 0;
    }

    /**
     * @return string
     */
    public function getGroupCode():string
    {
        return $this->groupCode;
    }

    /**
     * @param string $groupCode
     */
    public function setGroupCode(string $groupCode)
    {
        $this->groupCode = $groupCode;
    }

    /**
     * @return string
     */
    public function getGroupDescription()
    {
        return $this->groupDescription;
    }

    /**
     * @param string $groupDescription
     */
    public function setGroupDescription($groupDescription)
    {
        $this->groupDescription = $groupDescription;
    }


    public function getTestCount()
    {
        return $this->testCount;
    }

    public function setTestCount($testCount)
    {
        $this->testCount = $testCount;

        return $this;
    }
}