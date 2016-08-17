<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\ParameterCheck\StatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Calculator\TesterStatisticsCalculator;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Repository\TesterStatisticsRepository;
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

    private $calculator;

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
        $this->calculator = new TesterStatisticsCalculator();
    }

    public function getForSite($siteId, $year, $month)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);

        $validator = new StatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month)) {
            throw new NotFoundException("Site Statistics");
        }

        $statistics = $this->repository->getForSite($siteId, $year, $month);

        return $this->calculator->calculateStatisticsForSite($statistics);
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

        return $this->calculator->calculateStatisticsForTester($statistics);
    }
}
