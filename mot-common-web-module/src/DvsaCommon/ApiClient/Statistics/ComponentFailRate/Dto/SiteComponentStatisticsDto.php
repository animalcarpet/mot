<?php

namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class SiteComponentStatisticsDto implements ReflectiveDtoInterface
{
    private $siteId;
    private $group;
    private $monthRange;
    /** @var  \DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto[] */
    private $components;

    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
        return $this;
    }

    public function getSiteId()
    {
        return $this->siteId;
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

    public function getMonthRange()
    {
        return $this->monthRange;
    }

    public function setMonthRange($monthRange)
    {
        $this->monthRange = $monthRange;
        return $this;
    }
}
