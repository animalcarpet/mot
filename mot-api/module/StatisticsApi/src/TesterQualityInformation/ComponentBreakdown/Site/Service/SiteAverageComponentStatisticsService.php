<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator\DateRangeValidator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Repository\SiteAverageComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository\TesterAtSiteComponentStatisticsRepository;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProvider;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;

class SiteAverageComponentStatisticsService implements AutoWireableInterface
{
    private $authorisationService;
    private $averageComponentStatisticsRepository;
    private $siteAverageStatisticsCalculatorService;
    private $dateTimeHolderInterface;
    private $testerAtSiteComponentStatisticsRepository;
    private $identityProvider;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        SiteAverageComponentStatisticsRepository $averageComponentStatisticsRepository,
        SiteAverageStatisticsCalculatorService $siteAverageStatisticsCalculatorService,
        DateTimeHolderInterface $dateTimeHolderInterface,
        TesterAtSiteComponentStatisticsRepository $testerAtSiteComponentStatisticsRepository,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->authorisationService = $authorisationService;
        $this->averageComponentStatisticsRepository = $averageComponentStatisticsRepository;
        $this->siteAverageStatisticsCalculatorService = $siteAverageStatisticsCalculatorService;
        $this->dateTimeHolderInterface = $dateTimeHolderInterface;
        $this->testerAtSiteComponentStatisticsRepository = $testerAtSiteComponentStatisticsRepository;
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param $siteId
     * @param $group
     * @param $monthRange
     * @return SiteComponentStatisticsDto
     * @throws NotFoundException
     */
    public function get(int $siteId, string $group, int $monthRange)
    {
        if(!$this->authorisationService->isGrantedAtSite(PermissionAtSite::VTS_VIEW_AVERAGE_TEST_QUALITY, $siteId)){
            $this->assertUserDidTestInThisSite($siteId, $group);
        }

        if (!VehicleClassGroupCode::exists($group) || !(new DateRangeValidator())->isValid($monthRange)) {
            throw new NotFoundException('Site Average Component Statistics');
        }

        $monthDateRange = new LastMonthsDateRange($this->dateTimeHolderInterface);
        $monthDateRange->setNumberOfMonths($monthRange);

        $componentStatistics = $this->averageComponentStatisticsRepository->getComponentStatistics($siteId, $group, $monthDateRange);
        $statisticsTotalCount = $this->averageComponentStatisticsRepository->getTotalCount($siteId, $group, $monthDateRange);

        return $this->siteAverageStatisticsCalculatorService->calculate($siteId, $group, $monthRange, $componentStatistics, $statisticsTotalCount);
    }

    private function assertUserDidTestInThisSite(int $siteId, string $group)
    {
        $dateRange = (new LastMonthsDateRange($this->dateTimeHolderInterface))->setNumberOfMonths(LastMonthsDateRange::THREE_MONTHS);
        $data = $this->testerAtSiteComponentStatisticsRepository->get($this->identityProvider->getIdentity()->getUserId(), $siteId, $group, $dateRange);

        if(count($data) == 0){
            throw new UnauthorisedException('User is not authorised to view TQI site average, and didn\'t do any tests here');
        }
    }
}
