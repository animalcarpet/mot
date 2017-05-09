<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult;

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
