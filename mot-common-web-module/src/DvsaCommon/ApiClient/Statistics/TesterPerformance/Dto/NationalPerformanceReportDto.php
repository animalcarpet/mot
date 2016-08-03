<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\ApiClient\Statistics\Common\ReportDtoInterface;
use DvsaCommon\ApiClient\Statistics\Common\ReportStatusDto;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class NationalPerformanceReportDto implements ReflectiveDtoInterface, ReportDtoInterface
{
    private $month;
    private $year;
    /** @var MotTestingPerformanceDto */
    private $groupA;

    /** @var MotTestingPerformanceDto */
    private $groupB;

    /** @var ReportStatusDto */
    private $reportStatus;

    function __construct()
    {
        $this->reportStatus = new ReportStatusDto();
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function getGroupA()
    {
        return $this->groupA;
    }

    public function setGroupA(MotTestingPerformanceDto $groupA)
    {
        $this->groupA = $groupA;
        return $this;
    }

    public function getGroupB()
    {
        return $this->groupB;
    }

    public function setGroupB(MotTestingPerformanceDto $groupB)
    {
        $this->groupB = $groupB;
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
}
