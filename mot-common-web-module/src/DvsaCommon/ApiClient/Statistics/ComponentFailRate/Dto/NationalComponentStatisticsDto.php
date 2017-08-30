<?php

namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto;

use DvsaCommon\ApiClient\Statistics\Common\ReportDtoInterface;
use DvsaCommon\ApiClient\Statistics\Common\ReportStatusDto;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class NationalComponentStatisticsDto implements ReflectiveDtoInterface, ReportDtoInterface
{
    private $group;
    private $monthRange;
    /** @var  \DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto[] */
    private $components;

    /** @var ReportStatusDto */
    private $reportStatus;

    function __construct()
    {
        $this->reportStatus = new ReportStatusDto();
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return \DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto[]
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto[] $components
     * @return $this
     */
    public function setComponents($components)
    {
        $this->components = $components;
        return $this;
    }

    public function getReportStatus()
    {
        return $this->reportStatus;
    }

    public function setReportStatus(ReportStatusDto $reportStatus)
    {
        $this->reportStatus = $reportStatus;
        return $this;
    }

    public function getMonthRange()
    {
        return $this->monthRange;
    }

    public function setMonthRange($monthRange)
    {
        $this->monthRange = $monthRange;
    }
}
