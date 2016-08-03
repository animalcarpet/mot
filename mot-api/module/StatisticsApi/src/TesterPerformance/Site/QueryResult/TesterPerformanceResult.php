<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\QueryResult;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterPerformanceResult implements ReflectiveDtoInterface
{
    private $vehicleClassGroup;
    private $person_id;
    private $username;
    /**
     * @var int
     */
    private $totalTime;
    private $failedCount;
    private $totalCount;
    private $averageVehicleAgeInMonths;
    private $isAverageVehicleAgeAvailable;

    public function getVehicleClassGroup()
    {
        return $this->vehicleClassGroup;
    }

    public function setVehicleClassGroup($vehicleClassGroup)
    {
        $this->vehicleClassGroup = $vehicleClassGroup;
        return $this;
    }

    public function getPersonId()
    {
        return $this->person_id;
    }

    public function setPersonId($person_id)
    {
        $this->person_id = $person_id;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
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

    public function getTesterPerformanceStatisticsAsArray()
    {
        return [
            'vehicleClassGroup'            => $this->getVehicleClassGroup(),
            'person_id'                    => $this->getPersonId(),
            'username'                     => $this->getUsername(),
            'totalTime'                    => $this->getTotalTime(),
            'failedCount'                  => $this->getFailedCount(),
            'totalCount'                   => $this->getTotalCount(),
            'averageVehicleAgeInMonths'    => $this->getAverageVehicleAgeInMonths(),
            'isAverageVehicleAgeAvailable' => $this->getIsAverageVehicleAgeAvailable(),
        ];
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

