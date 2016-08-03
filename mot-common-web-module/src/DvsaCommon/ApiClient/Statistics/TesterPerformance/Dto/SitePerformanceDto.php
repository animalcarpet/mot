<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class SitePerformanceDto implements ReflectiveDtoInterface
{
    /** @var SiteGroupPerformanceDto */
    private $a;

    /** @var SiteGroupPerformanceDto */
    private $b;

    public function getA()
    {
        return $this->a;
    }

    public function setA(SiteGroupPerformanceDto $a)
    {
        $this->a = $a;
        return $this;
    }

    public function getB()
    {
        return $this->b;
    }

    public function setB(SiteGroupPerformanceDto $b)
    {
        $this->b = $b;
        return $this;
    }
}
