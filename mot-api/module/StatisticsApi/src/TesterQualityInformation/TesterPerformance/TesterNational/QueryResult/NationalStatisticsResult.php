<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\QueryResult;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class NationalStatisticsResult implements ReflectiveDtoInterface
{
    private $groupATotal;
    private $groupAFailed;
    private $groupACumulativeTestTime;
    private $groupAAverageVehicleAgeInMonths;
    private $isGroupAAverageVehicleAgeAvailable;
    private $numberOfGroupATesters;

    private $groupBTotal;
    private $groupBFailed;
    private $groupBCumulativeTestTime;
    private $groupBAverageVehicleAgeInMonths;
    private $isGroupBAverageVehicleAgeAvailable;
    private $numberOfGroupBTesters;

    public function getGroupATotal()
    {
        return $this->groupATotal;
    }

    public function setGroupATotal($groupATotal)
    {
        $this->groupATotal = $groupATotal;

        return $this;
    }

    public function getGroupAFailed()
    {
        return $this->groupAFailed;
    }

    public function setGroupAFailed($groupAFailed)
    {
        $this->groupAFailed = $groupAFailed;

        return $this;
    }

    public function getGroupACumulativeTestTime()
    {
        return $this->groupACumulativeTestTime;
    }

    public function setGroupACumulativeTestTime($groupACumulativeTestTime)
    {
        $this->groupACumulativeTestTime = $groupACumulativeTestTime;

        return $this;
    }

    public function getGroupBTotal()
    {
        return $this->groupBTotal;
    }

    public function setGroupBTotal($groupBTotal)
    {
        $this->groupBTotal = $groupBTotal;

        return $this;
    }

    public function getGroupBFailed()
    {
        return $this->groupBFailed;
    }

    public function setGroupBFailed($groupBFailed)
    {
        $this->groupBFailed = $groupBFailed;

        return $this;
    }

    public function getGroupBCumulativeTestTime()
    {
        return $this->groupBCumulativeTestTime;
    }

    public function setGroupBCumulativeTestTime($groupBCumulativeTestTime)
    {
        $this->groupBCumulativeTestTime = $groupBCumulativeTestTime;

        return $this;
    }

    public function getNumberOfGroupATesters()
    {
        return $this->numberOfGroupATesters;
    }

    public function setNumberOfGroupATesters($numberOfGroupATesters)
    {
        $this->numberOfGroupATesters = $numberOfGroupATesters;

        return $this;
    }

    public function getNumberOfGroupBTesters()
    {
        return $this->numberOfGroupBTesters;
    }

    public function setNumberOfGroupBTesters($numberOfGroupBTesters)
    {
        $this->numberOfGroupBTesters = $numberOfGroupBTesters;

        return $this;
    }

    public function getGroupAAverageVehicleAgeInMonths()
    {
        return $this->groupAAverageVehicleAgeInMonths;
    }

    public function setGroupAAverageVehicleAgeInMonths($groupAAverageVehicleAgeInMonths)
    {
        $this->groupAAverageVehicleAgeInMonths = $groupAAverageVehicleAgeInMonths;

        return $this;
    }

    public function getGroupBAverageVehicleAgeInMonths()
    {
        return $this->groupBAverageVehicleAgeInMonths;
    }

    public function setGroupBAverageVehicleAgeInMonths($groupBAverageVehicleAgeInMonths)
    {
        $this->groupBAverageVehicleAgeInMonths = $groupBAverageVehicleAgeInMonths;

        return $this;
    }

    public function getIsGroupAAverageVehicleAgeAvailable()
    {
        return $this->isGroupAAverageVehicleAgeAvailable;
    }

    public function setIsGroupAAverageVehicleAgeAvailable($isGroupAAverageVehicleAgeAvailable)
    {
        $this->isGroupAAverageVehicleAgeAvailable = $isGroupAAverageVehicleAgeAvailable;

        return $this;
    }

    public function getIsGroupBAverageVehicleAgeAvailable()
    {
        return $this->isGroupBAverageVehicleAgeAvailable;
    }

    public function setIsGroupBAverageVehicleAgeAvailable($isGroupBAverageVehicleAgeAvailable)
    {
        $this->isGroupBAverageVehicleAgeAvailable = $isGroupBAverageVehicleAgeAvailable;

        return $this;
    }
}
