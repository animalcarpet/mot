<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use Doctrine\DBAL\Types\Type;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;
use DvsaCommon\Guid\Guid;
use DvsaCommon\Utility\TypeCheck;

class MotTestingPerformanceDto implements ReflectiveDtoInterface
{
    private $total;

    /**
     * @var TimeSpan
     */
    private $averageTime;
    private $percentageFailed;
    private $averageVehicleAgeInMonths;
    private $isAverageVehicleAgeAvailable;

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return TimeSpan
     */
    public function getAverageTime()
    {
        return $this->averageTime;
    }

    public function setAverageTime(TimeSpan $averageTime)
    {
        TypeCheck::assertInstance($averageTime, TimeSpan::class);
        $this->averageTime = $averageTime;
        return $this;
    }

    public function getPercentageFailed()
    {
        return $this->percentageFailed;
    }

    public function setPercentageFailed($percentageFailed)
    {
        $this->percentageFailed = $percentageFailed;
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
