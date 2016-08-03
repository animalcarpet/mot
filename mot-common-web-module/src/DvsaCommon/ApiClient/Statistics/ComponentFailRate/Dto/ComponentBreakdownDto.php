<?php
namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class ComponentBreakdownDto implements ReflectiveDtoInterface
{
    /** @var  ComponentDto[] */
    private $components;
    /** @var  MotTestingPerformanceDto */
    private $groupPerformance;
    /** @var  string */
    private $userName;
    /** @var string */
    private $displayName;

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

    /**
     * @return MotTestingPerformanceDto
     */
    public function getGroupPerformance()
    {
        return $this->groupPerformance;
    }

    public function setGroupPerformance(MotTestingPerformanceDto $groupPerformance)
    {
        $this->groupPerformance = $groupPerformance;
        return $this;
    }
    public function getUserName()
    {
        return $this->userName;
    }
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

}