<?php
namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterMultiSitePerformanceDto extends MotTestingPerformanceDto implements ReflectiveDtoInterface
{
    private $siteId;
    private $siteName;
    private $siteAddress;

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     * @return $this
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     * @return $this
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
        return $this;
    }

    /**
     * @return \DvsaCommon\Dto\Contact\AddressDto
     */
    public function getSiteAddress()
    {
        return $this->siteAddress;
    }

    /**
     * @param \DvsaCommon\Dto\Contact\AddressDto $siteAddress
     * @return $this
     */
    public function setSiteAddress($siteAddress)
    {
        $this->siteAddress = $siteAddress;
        return $this;
    }
}