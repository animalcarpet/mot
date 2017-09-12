<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper\ComponentBreakdownDtoMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository\MultipleTestersAtSiteComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterStatisticsRepository;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class MultipleTestersAtSiteComponentStatisticsService implements AutoWireableInterface
{
    private $componentStatisticsForTestersAtSiteRepository;
    private $testerStatisticsRepository;
    private $authorisationService;
    private $dtoMapper;
    private $dateTimeHolderInterface;

    public function __construct(
        MultipleTestersAtSiteComponentStatisticsRepository $componentStatisticsForTestersAtSiteRepository,
        TesterStatisticsRepository $testerStatistic,
        MotAuthorisationServiceInterface $authorisationService,
        ComponentBreakdownDtoMapper $dtoMapper,
        DateTimeHolderInterface $dateTimeHolderInterface
    ) {
        $this->componentStatisticsForTestersAtSiteRepository = $componentStatisticsForTestersAtSiteRepository;
        $this->testerStatisticsRepository = $testerStatistic;
        $this->authorisationService = $authorisationService;
        $this->dtoMapper = $dtoMapper;
        $this->dateTimeHolderInterface = $dateTimeHolderInterface;
    }

    /**
     * @param int $siteId
     * @param string $group
     * @param int $monthRange
     * @return ComponentBreakdownDto[]
     */
    public function get(int $siteId, string $group, int $monthRange): array
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);

        $monthDateRange = new LastMonthsDateRange($this->dateTimeHolderInterface);
        $monthDateRange->setNumberOfMonths($monthRange);

        $components = $this->componentStatisticsForTestersAtSiteRepository->get($siteId, $group, $monthDateRange);
        $testerPerformance = $this->testerStatisticsRepository->getForSite($siteId, $monthDateRange);

        return $this->dtoMapper->mapQueryResultsForMultipleTesters($components, $testerPerformance, $group);
    }
}
