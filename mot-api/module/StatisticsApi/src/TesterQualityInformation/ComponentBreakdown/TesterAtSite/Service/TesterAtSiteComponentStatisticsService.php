<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper\ComponentBreakdownDtoMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck\GroupStatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository\TesterAtSiteComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterAtSiteStatisticsRepository;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use PersonApi\Service\PersonalDetailsService;

class TesterAtSiteComponentStatisticsService implements AutoWireableInterface
{
    private $componentStatisticsRepository;
    private $dateTimeHolder;
    private $testerStatisticsRepository;
    private $authorisationService;
    private $personContactService;
    private $dtoMapper;

    public function __construct(
        TesterAtSiteComponentStatisticsRepository $componentStatisticsRepository,
        TesterAtSiteStatisticsRepository $testerStatisticsRepository,
        DateTimeHolder $dateTimeHolder,
        MotAuthorisationServiceInterface $authorisationService,
        PersonalDetailsService $personalDetailsService,
        ComponentBreakdownDtoMapper $dtoMapper
    )
    {
        $this->componentStatisticsRepository = $componentStatisticsRepository;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->testerStatisticsRepository = $testerStatisticsRepository;
        $this->authorisationService = $authorisationService;
        $this->personContactService = $personalDetailsService;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->dtoMapper = $dtoMapper;
    }

    public function get($siteId, $testerId, $group, $year, $month)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);

        $validator = new GroupStatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month, $group)) {
            throw new NotFoundException("Tester Component Statistics");
        }

        $components = $this->componentStatisticsRepository->get($testerId, $siteId, $group, $year, $month);
        $testerPerformance = $this->testerStatisticsRepository->get($siteId, $testerId, $group, $year, $month);
        $user = $this->personContactService->findPerson($testerId);

        return $this->dtoMapper->mapQueryResultsToComponentBreakdownDto($components, $testerPerformance, $user);
    }
}