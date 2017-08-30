<?php

namespace Dvsa\Mot\Frontend\TestQualityInformation\ViewModel;

use Core\Formatting\ComponentFailRateFormatter;
use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use Site\ViewModel\TimeSpanFormatter;
use DvsaCommon\Utility\TypeCheck;

class ComponentStatisticsTable
{
    const TEXT_EMPTY = 'n/a';
    const DEFAULT_TESTER_AVERAGE = 0;
    const TEXT_NOT_AVAILABLE = 'Not available';

    private $componentRows;
    /** @var bool */
    private $isNationalAverageAvailable;
    /** @var AverageGroupStatisticsHeader */
    private $averageGroupStatisticsHeader;

    public function __construct(
        ComponentBreakdownDto $breakdownDto,
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        array $siteAverageBreakdown,
        $groupDescription, $groupCode
    ) {
        TypeCheck::assertCollectionOfClass($siteAverageBreakdown, ComponentDto::class);

        $this->isNationalAverageAvailable = $nationalComponentStatisticsDto->getReportStatus()->getIsCompleted();
        $motTestingPerformanceDto = $breakdownDto->getGroupPerformance();

        $this->componentRows = $this->createComponentRows(
            $breakdownDto->getComponents(),
            $nationalComponentStatisticsDto->getComponents(),
            $siteAverageBreakdown
        );

        $this->averageGroupStatisticsHeader = $this->createAverageGroupStatisticsHeader($motTestingPerformanceDto, $groupDescription, $groupCode);
    }

    private function createAverageGroupStatisticsHeader(MotTestingPerformanceDto $motTestingPerformanceDto, $groupDescription, $groupCode):AverageGroupStatisticsHeader
    {
        $timeSpanFormatter = new TimeSpanFormatter();
        $averageGroupStatistics = new AverageGroupStatisticsHeader();
        $averageGroupStatistics->setTestCount($motTestingPerformanceDto->getTotal());
        $averageGroupStatistics->setIsAverageVehicleAgeAvailable(
            !is_null($motTestingPerformanceDto->getIsAverageVehicleAgeAvailable())
                ? $motTestingPerformanceDto->getIsAverageVehicleAgeAvailable()
                : false
        );
        $averageGroupStatistics->setAverageTestDuration(
            !is_null($motTestingPerformanceDto->getAverageTime())
                ? $timeSpanFormatter->formatForTestQualityInformationView($motTestingPerformanceDto->getAverageTime())
                : 0
        );
        $averageGroupStatistics->setFailurePercentage($this->numberFormat(
            !is_null($motTestingPerformanceDto->getPercentageFailed())
                ? $motTestingPerformanceDto->getPercentageFailed()
                : 0

        ));
        $averageGroupStatistics->setGroupDescription($groupDescription);
        $averageGroupStatistics->setGroupCode($groupCode);
        $averageGroupStatistics->setAverageVehicleAge($this->determineVehicleAge($motTestingPerformanceDto));

        $averageGroupStatistics->setHasTests(!is_null($motTestingPerformanceDto->getAverageTime()) && $motTestingPerformanceDto->getTotal() !== 0);
        return $averageGroupStatistics;
    }

    public function isNationalAverageAvailable()
    {
        return $this->isNationalAverageAvailable;
    }

    /**
     * @return ComponentStatisticsRow[]
     */
    public function getComponentRows()
    {
        return $this->componentRows;
    }

    /**
     * @param ComponentDto[] $userComponents
     * @param ComponentDto[] $nationalComponents
     * @param ComponentDto[] $siteAverageComponents
     *
     * @return ComponentStatisticsRow[]
     */
    private function createComponentRows($userComponents, $nationalComponents, $siteAverageComponents)
    {
        $rows = [];

        foreach ($userComponents as $userComponent) {
            $nationalComponent = $this->getTesterDataByComponentId($userComponent->getId(), $nationalComponents);
            $siteAverageComponent = $this->getTesterDataByComponentId($userComponent->getId(), $siteAverageComponents);
            $rows[] = (new ComponentStatisticsRow())
                ->setCategoryId($userComponent->getId())
                ->setCategoryName($userComponent->getName())
                ->setTesterAverage(
                    $userComponent
                        ? ComponentFailRateFormatter::format($userComponent->getPercentageFailed())
                        : self::DEFAULT_TESTER_AVERAGE
                )
                ->setNationalAverage(
                    $this->isNationalAverageAvailable
                        ? ComponentFailRateFormatter::format($nationalComponent->getPercentageFailed())
                        : 0
                )
                ->setSiteAverage(
                    ($siteAverageComponent && $siteAverageComponent->getPercentageFailed())
                        ? ComponentFailRateFormatter::format($siteAverageComponent->getPercentageFailed())
                        : self::DEFAULT_TESTER_AVERAGE
                );
        }

        return $rows;
    }

    /**
     * @param int            $componentId
     * @param ComponentDto[] $userComponents
     *
     * @return ComponentDto
     */
    private function getTesterDataByComponentId($componentId, $userComponents)
    {
        foreach ($userComponents as $component) {
            if ($component->getId() == $componentId) {
                return $component;
            }
        }

        return null;
    }

    /**
     * @param string $value
     * @param string $defaultValue
     * @param string $appendIfNotEmpty
     *
     * @return string
     */
    protected function getNotEmptyText($value, $defaultValue = self::TEXT_EMPTY, $appendIfNotEmpty = '')
    {
        if (!is_null($value)) {
            return $value.$appendIfNotEmpty;
        } else {
            return $defaultValue;
        }
    }

    /**
     * @param MotTestingPerformanceDto $motTestingPerformanceDto
     *
     * @return int
     */
    protected function determineVehicleAge(MotTestingPerformanceDto $motTestingPerformanceDto):int
    {
        $age = 0;

        if ($motTestingPerformanceDto->getIsAverageVehicleAgeAvailable()) {
            $age = VehicleAgeFormatter::calculateVehicleAge($motTestingPerformanceDto->getAverageVehicleAgeInMonths());
        }

        return $age;
    }

    /**
     * @param float $number
     *
     * @return int|string
     */
    protected function numberFormat($number)
    {
        return is_numeric($number) ? round($number) : $number;
    }

    /**
     * @return AverageGroupStatisticsHeader
     */
    public function getAverageGroupStatisticsHeader():AverageGroupStatisticsHeader
    {
        return $this->averageGroupStatisticsHeader;
    }
}
