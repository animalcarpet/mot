<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\ParameterCheck\StatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Mapper\TesterStatisticsMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterStatisticsRepository;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;

class TesterStatisticsService implements AutoWireableInterface
{
    private $repository;

    private $authorisationService;

    private $viewTesterTestQualityAssertion;

    private $testerGroupAuthorisationMapper;

    private $dateTimeHolder;

    private $mapper;

    function __construct(
        TesterStatisticsRepository $repository,
        MotAuthorisationServiceInterface $authorisationService,
        ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        DateTimeHolder $dateTimeHolder
    )
    {
        $this->repository = $repository;
        $this->authorisationService = $authorisationService;
        $this->viewTesterTestQualityAssertion = $viewTesterTestQualityAssertion;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->mapper = new TesterStatisticsMapper();
    }

    public function getForSite($siteId, $year, $month)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);

        $validator = new StatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month)) {
            throw new NotFoundException("Site Statistics");
        }

        $statistics = $this->repository->getForSite($siteId, $year, $month);

        return $this->mapper->buildSitePerformanceDto($statistics);
    }

    public function getForTester($testerId, $year, $month)
    {
        $authorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($testerId);
        $this->viewTesterTestQualityAssertion->assertGranted($testerId, $authorisation);

        $validator = new StatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month)) {
            throw new NotFoundException("Tester Statistics");
        }

        $statistics = $this->repository->getForTester($testerId, $year, $month);

        return $this->mapper->buildTesterPerformanceDto($statistics);
    }
}
