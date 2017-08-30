<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Service;

use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Mapper\TesterStatisticsMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterStatisticsRepository;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;

class TesterStatisticsService implements AutoWireableInterface
{
    private $repository;

    private $authorisationService;

    private $viewTesterTestQualityAssertion;

    private $testerGroupAuthorisationMapper;

    private $mapper;

    private $dateTimeHolder;

    public function __construct(
        TesterStatisticsRepository $repository,
        MotAuthorisationServiceInterface $authorisationService,
        ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        DateTimeHolderInterface $dateTimeHolder
    ) {
        $this->repository = $repository;
        $this->authorisationService = $authorisationService;
        $this->viewTesterTestQualityAssertion = $viewTesterTestQualityAssertion;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->mapper = new TesterStatisticsMapper();
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function getForSite(int $siteId, int $monthRange)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);
        $dateRange = (new LastMonthsDateRange($this->dateTimeHolder))->setNumberOfMonths($monthRange);

        $statistics = $this->repository->getForSite($siteId, $dateRange);

        return $this->mapper->buildSitePerformanceDto($statistics);
    }

    public function getForTester(int $testerId, int $monthRange)
    {
        $authorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($testerId);
        $this->viewTesterTestQualityAssertion->assertGranted($testerId, $authorisation);

        $lastMonthsDateRange = new LastMonthsDateRange($this->dateTimeHolder);
        $lastMonthsDateRange->setNumberOfMonths($monthRange);

        $statistics = $this->repository->getForTester($testerId, $lastMonthsDateRange);

        return $this->mapper->buildTesterPerformanceDto($statistics);
    }
}
