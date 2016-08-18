<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper\ComponentBreakdownDtoMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck\GroupStatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterStatisticsRepository;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;
use PersonApi\Service\PersonalDetailsService;

class TesterComponentStatisticsService implements AutoWireableInterface
{
    private $componentStatisticsRepository;
    private $dateTimeHolder;
    private $testerStatisticsRepository;
    private $authorisationService;
    private $personContactService;
    private $dtoMapper;
    private $testerGroupAuthorisationMapper;
    private $assertion;

    public function __construct(
        TesterComponentStatisticsRepository $componentStatisticsRepository,
        TesterStatisticsRepository $testerStatisticsRepository,
        DateTimeHolder $dateTimeHolder,
        MotAuthorisationServiceInterface $authorisationService,
        PersonalDetailsService $personalDetailsService,
        ViewTesterTestQualityAssertion $assertion,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
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
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->assertion = $assertion;
    }

    public function get($testerId, $group, $year, $month)
    {
        $authorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($testerId);
        $this->assertion->assertGranted($testerId, $authorisation);

        $validator = new GroupStatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month, $group)) {
            throw new NotFoundException("Tester Component Statistics");
        }

        $components = $this->componentStatisticsRepository->get($testerId, $group, $year, $month);
        $testerPerformance = $this->testerStatisticsRepository->get($testerId, $group, $year, $month);
        $user = $this->personContactService->findPerson($testerId);

        return $this->dtoMapper->mapQueryResultsToComponentBreakdownDto($components, $testerPerformance, $user);
    }
}