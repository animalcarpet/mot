<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

/**
 * todo Jareq is this even used
 *
 * Class TesterGroupPerformanceDto
 * @package DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto
 */
class TesterGroupPerformanceDto implements ReflectiveDtoInterface
{

    /** @var \DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto */
    private $statistics;

    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto $statistics
     * @return $this
     */
    public function setStatistics($statistics)
    {
        $this->statistics = $statistics;
        return $this;
    }
}
