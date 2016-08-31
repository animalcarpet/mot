<?php
namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterMultiSitePerformanceReportDto implements ReflectiveDtoInterface
{
    /** @var  TesterMultiSitePerformanceDto[] */
    private $a;
    /** @var  TesterMultiSitePerformanceDto[] */
    private $b;

    /**
     * @return TesterMultiSitePerformanceDto[]
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceDto[] $a
     */
    public function setA(array $a)
    {
        $this->a = $a;
    }

    /**
     * @return TesterMultiSitePerformanceDto[]
     */
    public function getB()
    {
        return $this->b;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceDto[] $b
     */
    public function setB(array $b)
    {
        $this->b = $b;
    }
}