<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\ParameterCheck\GroupStatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\QueryResult\TesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Repository\TesterComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Repository\TesterStatisticsRepository;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Person;
use PersonApi\Service\PersonalDetailsService;

class TesterComponentStatisticsService implements AutoWireableInterface
{
    private $componentStatisticsRepository;
    private $dateTimeHolder;
    private $testerStatisticsRepository;
    private $authorisationService;
    private $personContactService;

    public function __construct(
        TesterComponentStatisticsRepository $componentStatisticsRepository,
        TesterStatisticsRepository $testerStatisticsRepository,
        DateTimeHolder $dateTimeHolder,
        MotAuthorisationServiceInterface $authorisationService,
        PersonalDetailsService $personalDetailsService
    )
    {
        $this->componentStatisticsRepository = $componentStatisticsRepository;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->testerStatisticsRepository = $testerStatisticsRepository;
        $this->authorisationService = $authorisationService;
        $this->personContactService = $personalDetailsService;
        $this->dateTimeHolder = $dateTimeHolder;
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

        return $this->buildComponentDtosFromQueryResults($components, $testerPerformance, $user);
    }

    /**
     * @param $components ComponentFailRateResult[]
     * @param TesterPerformanceResult $testerPerformance
     * @param Person $person
     * @return ComponentBreakdownDto
     */
    private function buildComponentDtosFromQueryResults($components, $testerPerformance, Person $person)
    {
        $componentBreakdownDto = new ComponentBreakdownDto();

        if (!empty($components)) {
            $componentDtos = [];
            foreach ($components as $component) {
                $componentDto = new ComponentDto();
                $componentDto->setPercentageFailed($testerPerformance->getFailedCount() ?
                    100 * $component->getFailedCount() / $testerPerformance->getFailedCount() : 0)
                    ->setName($component->getTestItemCategoryName())
                    ->setId($component->getTestItemCategoryId());

                $componentDtos[] = $componentDto;
            }

            $componentBreakdownDto->setComponents($componentDtos);
        }

        $groupPerformanceDto = new MotTestingPerformanceDto();
        $groupPerformanceDto->setPercentageFailed($testerPerformance->getTotalCount() ?
            100 * $testerPerformance->getFailedCount() / $testerPerformance->getTotalCount() : 0)
            ->setTotal($testerPerformance->getTotalCount())
            ->setAverageVehicleAgeInMonths($testerPerformance->getAverageVehicleAgeInMonths())
            ->setIsAverageVehicleAgeAvailable($testerPerformance->getIsAverageVehicleAgeAvailable())
            ->setAverageTime(new TimeSpan(0, 0, 0, $testerPerformance->getTotalCount() ?
                $testerPerformance->getTotalTime() / $testerPerformance->getTotalCount() : 0));
        $componentBreakdownDto->setGroupPerformance($groupPerformanceDto);
        $componentBreakdownDto->setUserName($person->getUsername());
        $componentBreakdownDto->setDisplayName($person->getDisplayName());

        return $componentBreakdownDto;
    }
}