<?php

namespace Site\Action;

use Core\Action\FileAction;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\SiteComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Csv\TQI\SiteTestQualityCsvBuilder;

class SiteTestQualityCsvAction implements AutoWireableInterface
{
    private $sitePerformanceApiResource;
    private $componentFailRateApiResource;
    private $siteComponentFailRateApiResource;
    private $nationalPerformanceApiResource;
    private $nationalComponentStatisticApiResource;
    private $assertion;
    private $siteMapper;
    private $dateTimeHolder;

    public function __construct(
        SitePerformanceApiResource $sitePerformanceApiResource,
        ComponentFailRateApiResource $componentFailRateApiResource,
        SiteComponentFailRateApiResource $siteComponentFailRateApiResource,
        NationalComponentStatisticApiResource $nationalComponentStatisticApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        SiteMapper $siteMapper,
        ViewVtsTestQualityAssertion $assertion,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->sitePerformanceApiResource = $sitePerformanceApiResource;
        $this->componentFailRateApiResource = $componentFailRateApiResource;
        $this->siteComponentFailRateApiResource = $siteComponentFailRateApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->nationalComponentStatisticApiResource = $nationalComponentStatisticApiResource;
        $this->siteMapper = $siteMapper;
        $this->assertion = $assertion;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function execute(int $siteId, int $monthRange, string $groupCode): FileAction
    {
        $this->assertion->assertGranted($siteId);

        $site = $this->siteMapper->getById($siteId);
        $nationalPerformance = $this->nationalPerformanceApiResource->getForMonths($monthRange);
        $nationalComponentBreakdown = $this->nationalComponentStatisticApiResource->getForDate($groupCode, $monthRange);
        $sitePerformance = $this->sitePerformanceApiResource->getForMonthRange($siteId, $monthRange);
        $componentBreakdownForTesters = $this->componentFailRateApiResource->getForAllTestersAtSite($siteId, $groupCode, $monthRange);
        $siteComponentStatisticsDto = $this->siteComponentFailRateApiResource->get($siteId, $groupCode, $monthRange);

        $lastMonthsRange = new LastMonthsDateRange($this->dateTimeHolder);
        $lastMonthsRange->setNumberOfMonths($monthRange);

        $csvBuilder= $this->getCsvBuilder(
            $groupCode,
            $sitePerformance,
            $componentBreakdownForTesters,
            $siteComponentStatisticsDto,
            $nationalComponentBreakdown,
            $nationalPerformance,
            $lastMonthsRange,
            $site
        );

        return new FileAction($csvBuilder->toCsvFile());
    }

    private function getCsvBuilder(
        string $groupCode,
        SitePerformanceDto $sitePerformance,
        array $componentBreakdownForTesters,
        SiteComponentStatisticsDto $siteComponentStatisticsDto,
        NationalComponentStatisticsDto $nationalComponentBreakdown,
        NationalPerformanceReportDto $nationalPerformance,
        LastMonthsDateRange $lastMonthsRange,
        VehicleTestingStationDto $vts
    ): SiteTestQualityCsvBuilder
    {
        switch ($groupCode) {
            case VehicleClassGroupCode::BIKES:
                $groupSitePerformance = $sitePerformance->getA();
                $groupNationalPerformance = $nationalPerformance->getGroupA();
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $groupSitePerformance = $sitePerformance->getB();
                $groupNationalPerformance = $nationalPerformance->getGroupB();
                break;
            default:
                throw new \InvalidArgumentException('Wrong group code');
        }

        return new SiteTestQualityCsvBuilder(
            $groupSitePerformance,
            $componentBreakdownForTesters,
            $siteComponentStatisticsDto,
            $nationalComponentBreakdown,
            $nationalPerformance->getReportStatus()->getIsCompleted(),
            $groupNationalPerformance,
            $vts,
            $groupCode,
            $lastMonthsRange
        );
    }
}
