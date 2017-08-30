<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper\ComponentBreakdownDtoMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator\DateRangeValidator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository\TesterAtSiteComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterAtSiteSingleGroupStatisticsRepository;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Repository\SiteRepository;
use PersonApi\Service\PersonalDetailsService;

class TesterAtSiteComponentStatisticsService implements AutoWireableInterface
{
    private $componentStatisticsRepository;
    private $testerStatisticsRepository;
    private $authorisationService;
    private $personContactService;
    private $dtoMapper;
    private $identityProvider;
    private $dateTimeHolderInterface;
    private $siteRepository;

    public function __construct(
        TesterAtSiteComponentStatisticsRepository $componentStatisticsRepository,
        TesterAtSiteSingleGroupStatisticsRepository $testerStatisticsRepository,
        MotAuthorisationServiceInterface $authorisationService,
        PersonalDetailsService $personalDetailsService,
        ComponentBreakdownDtoMapper $dtoMapper,
        MotIdentityProviderInterface $identityProvider,
        DateTimeHolderInterface $dateTimeHolderInterface,
        SiteRepository $siteRepository
    ) {
        $this->componentStatisticsRepository = $componentStatisticsRepository;
        $this->testerStatisticsRepository = $testerStatisticsRepository;
        $this->authorisationService = $authorisationService;
        $this->personContactService = $personalDetailsService;
        $this->dtoMapper = $dtoMapper;
        $this->identityProvider = $identityProvider;
        $this->dateTimeHolderInterface = $dateTimeHolderInterface;
        $this->siteRepository = $siteRepository;
    }

    public function get($siteId, $testerId, $group, $monthRange)
    {
        if ($this->identityProvider->getIdentity()->getUserId() != $testerId) {
            $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);
        }

        if (!VehicleClassGroupCode::exists($group) || !(new DateRangeValidator())->isValid($monthRange)) {
            throw new NotFoundException('Tester At Site Component Statistics');
        }

        $monthDateRange = new LastMonthsDateRange($this->dateTimeHolderInterface);
        $monthDateRange->setNumberOfMonths($monthRange);

        $components = $this->componentStatisticsRepository->get($testerId, $siteId, $group, $monthDateRange);
        $testerPerformance = $this->testerStatisticsRepository->get($siteId, $testerId, $group, $monthDateRange);
        $user = $this->personContactService->findPerson($testerId);

        $componentBreakdownDto = $this->dtoMapper->mapQueryResultsToComponentBreakdownDto($components, $testerPerformance, $user);
        if(empty($componentBreakdownDto->getSiteName())){
            $componentBreakdownDto->setSiteName($this->siteRepository->get($siteId)->getName());
        }

        return $componentBreakdownDto;
    }
}
