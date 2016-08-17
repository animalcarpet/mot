<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterPerformanceDto implements ReflectiveDtoInterface
{
    /** @var EmployeePerformanceDto */
    private $groupAPerformance;

    /** @var EmployeePerformanceDto */
    private $groupBPerformance;

    public function getGroupAPerformance()
    {
        return $this->groupAPerformance;
    }

    public function setGroupAPerformance(EmployeePerformanceDto $groupPerformance = null)
    {
        $this->groupAPerformance = $groupPerformance;
        return $this;
    }

    public function getGroupBPerformance()
    {
        return $this->groupBPerformance;
    }

    public function setGroupBPerformance(EmployeePerformanceDto $groupPerformance = null)
    {
        $this->groupBPerformance = $groupPerformance;
        return $this;
    }
}
