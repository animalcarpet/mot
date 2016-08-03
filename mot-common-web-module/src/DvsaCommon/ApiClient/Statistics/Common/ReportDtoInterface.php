<?php

namespace DvsaCommon\ApiClient\Statistics\Common;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

interface ReportDtoInterface extends ReflectiveDtoInterface
{
    /**
     * @return \DvsaCommon\ApiClient\Statistics\Common\ReportStatusDto
     */
    public function getReportStatus();

    public function setReportStatus(ReportStatusDto $report);
}
