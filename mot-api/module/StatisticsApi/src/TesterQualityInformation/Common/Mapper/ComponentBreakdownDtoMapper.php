<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\Person;

class ComponentBreakdownDtoMapper implements AutoWireableInterface
{
    /**
     * @param $components ComponentFailRateResult[]
     * @param TesterAtSitePerformanceResult $testerPerformance
     * @param Person                        $person
     *
     * @return ComponentBreakdownDto
     */
    public function mapQueryResultsToComponentBreakdownDto(array $components,
       TesterAtSitePerformanceResult $testerPerformance = null, Person $person): ComponentBreakdownDto
    {
        $componentBreakdownDto = new ComponentBreakdownDto();

        $componentDtos = [];
        if (!empty($components)) {
            foreach ($components as $component) {
                $componentDto = new ComponentDto();
                $componentDto
                    ->setName($component->getTestItemCategoryName())
                    ->setId($component->getTestItemCategoryId());

                if($testerPerformance){
                    $componentDto->setPercentageFailed($testerPerformance->getFailedCount() ?
                    100 * $component->getFailedCount() / $testerPerformance->getFailedCount() : 0);
                }

                $componentDtos[] = $componentDto;
            }

            $componentBreakdownDto->setComponents($componentDtos);
        }

        $groupPerformanceDto = new MotTestingPerformanceDto();
        if($testerPerformance){
            $groupPerformanceDto->setPercentageFailed($testerPerformance->getTotalCount() ?
                100 * $testerPerformance->getFailedCount() / $testerPerformance->getTotalCount() : 0)
                ->setTotal($testerPerformance->getTotalCount())
                ->setAverageVehicleAgeInMonths($testerPerformance->getAverageVehicleAgeInMonths())
                ->setIsAverageVehicleAgeAvailable($testerPerformance->getIsAverageVehicleAgeAvailable())
                ->setAverageTime(new TimeSpan(0, 0, 0, $testerPerformance->getTotalCount() ?
                    $testerPerformance->getTotalTime() / $testerPerformance->getTotalCount() : 0));

            $componentBreakdownDto->setSiteName($testerPerformance->getSiteName());
        }

        $componentBreakdownDto->setGroupPerformance($groupPerformanceDto);
        $componentBreakdownDto->setUserName($person->getUsername());
        $componentBreakdownDto->setDisplayName($person->getDisplayName());

        return $componentBreakdownDto;
    }

    /**
     * @param ComponentFailRateResult[] $allTestersComponents
     * @param TesterAtSitePerformanceResult[] $testersPerformance
     * @return ComponentBreakdownDto[]
     */
    public function mapQueryResultsForMultipleTesters(array $allTestersComponents, array $testersPerformance, $group)
    {
        $componentBreakdownForTesters = [];
        foreach($testersPerformance as $singleTesterPerformance) {
            if ($singleTesterPerformance->getVehicleClassGroup() === $group) {
                $personId = $singleTesterPerformance->getPersonId();
                $singleTesterComponents = $this->filterComponentsForTester($allTestersComponents, $personId);
                $person = new Person();
                $person->setId($personId);
                $person->setUsername($singleTesterPerformance->getUsername());
                $componentBreakdownForTesters[] = $this->mapQueryResultsToComponentBreakdownDto($singleTesterComponents, $singleTesterPerformance, $person);
            }
        }
        return $componentBreakdownForTesters;
    }

    /**
     * @param ComponentFailRateResult[] $allTestersComponents
     * @param int $testerId
     * @return ComponentFailRateResult[]
     */
    private function filterComponentsForTester(array $allTestersComponents, int $testerId): array
    {
       return array_filter(
           $allTestersComponents,
           function(ComponentFailRateResult $component) use ($testerId) {
               return $component->getTesterId() == $testerId;
           }
       );
    }
}
