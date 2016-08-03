<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class SiteGroupPerformanceDto implements ReflectiveDtoInterface
{
    /** @var MotTestingPerformanceDto */
    private $total;

    /** @var \DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto[] */
    private $statistics;

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal(MotTestingPerformanceDto $total)
    {
        $this->total = $total;
        return $this;
    }

    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto[] $statistics
     * @return $this
     */
    public function setStatistics($statistics)
    {
        $this->statistics = $statistics;
        return $this;
    }
}
