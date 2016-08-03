<?php

namespace DvsaCommon\ApiClient\Statistics\AePerformance\Dto;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class AuthorisedExaminerSitesPerformanceDto implements ReflectiveDtoInterface
{

    /** @var  int */
    protected $siteTotalCount;
    /** @var  SiteDto[] */
    protected $sites;

    /**
     * @return int
     */
    public function getSiteTotalCount()
    {
        return $this->siteTotalCount;
    }

    /**
     * @param int $siteTotalCount
     * @return AuthorisedExaminerSitesPerformanceDto
     */
    public function setSiteTotalCount($siteTotalCount)
    {
        $this->siteTotalCount = $siteTotalCount;
        return $this;
    }

    /**
     * @return SiteDto[]
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto[] $sites
     * @return AuthorisedExaminerSitesPerformanceDto
     */
    public function setSites($sites)
    {
        $this->sites = $sites;
        return $this;
    }
}
