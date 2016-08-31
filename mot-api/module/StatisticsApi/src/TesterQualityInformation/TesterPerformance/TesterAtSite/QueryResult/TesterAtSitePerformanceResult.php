<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryResult\AbstractTesterPerformanceResult;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterAtSitePerformanceResult extends TesterPerformanceResult
{
    private $siteName;

    public function getSiteName()
    {
        return $this->siteName;
    }

    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
        return $this;
    }
}
