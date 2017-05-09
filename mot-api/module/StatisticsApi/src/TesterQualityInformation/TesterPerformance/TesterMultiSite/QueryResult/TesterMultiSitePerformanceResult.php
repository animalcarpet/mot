<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\QueryResult;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryResult\AbstractTesterPerformanceResult;
use DvsaEntities\Entity\Address;

class TesterMultiSitePerformanceResult extends AbstractTesterPerformanceResult
{
    private $siteId;
    private $siteName;
    private $siteAddress;

    /**
     * @return Address
     */
    public function getSiteAddress()
    {
        return $this->siteAddress;
    }

    /**
     * @param Address $address
     *
     * @return $this
     */
    public function setSiteAddress($address)
    {
        $this->siteAddress = $address;

        return $this;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;

        return $this;
    }

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
