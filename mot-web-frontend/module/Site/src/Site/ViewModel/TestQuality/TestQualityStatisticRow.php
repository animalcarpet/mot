<?php

namespace Site\ViewModel\TestQuality;

class TestQualityStatisticRow
{
    private $name;
    private $testCount;
    private $averageTestDuration;
    private $averageVehicleAge;
    private $failurePercentage;
    private $personId;
    private $groupCode;
    private $siteId;
    private $fullName;

    public function getPersonId()
    {
        return $this->personId;
    }

    public function setPersonId($personId)
    {
        $this->personId = $personId;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function hasTests()
    {
        return $this->testCount > 0;
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

    public function getAverageTestDuration()
    {
        return $this->averageTestDuration;
    }

    public function setAverageTestDuration($averageTestDuration)
    {
        $this->averageTestDuration = $averageTestDuration;

        return $this;
    }

    public function getFailurePercentage()
    {
        if ($this->getTestCount() > 0) {
            return number_format($this->failurePercentage, 0);
        } else {
            return 0;
        }
    }

    public function setFailurePercentage($failurePercentage)
    {
        $this->failurePercentage = $failurePercentage;

        return $this;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;

        return $this;
    }

    public function getUserStatisticsLinkParams()
    {
        return [
            'id' => $this->getSiteId(),
            'userId' => $this->getPersonId(),
            'group' => $this->getGroupCode(),
        ];
    }

    public function getGroupCode()
    {
        return $this->groupCode;
    }

    public function setGroupCode($groupCode)
    {
        $this->groupCode = $groupCode;

        return $this;
    }

    public function hasStatistics()
    {
        return $this->getTestCount() || $this->getAverageTestDuration() || $this->getFailurePercentage();
    }

    public function getAverageVehicleAge()
    {
        return $this->averageVehicleAge;
    }

    public function setAverageVehicleAge($averageVehicleAge)
    {
        $this->averageVehicleAge = $averageVehicleAge;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return TestQualityStatisticRow
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }
}
