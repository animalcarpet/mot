<?php
namespace Site\ViewModel\TestQuality;

use Core\Formatting\ComponentFailRateFormatter;
use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;

class ComponentStatisticsTable
{
    const TEXT_EMPTY = 'n/a';
    private $testCount;
    const DEFAULT_TESTER_AVERAGE = 0;
    const TEXT_NOT_AVAILABLE = 'Not available';
    /**
     * @var TimeSpan
     */
    private $averageTestDuration;
    private $failurePercentage;
    private $componentRows;
    private $groupDescription;
    private $groupCode;
    private $averageVehicleAge;
    /** @var boolean */
    private $isNationalAverageAvailable;

    public function __construct(
        ComponentBreakdownDto $breakdownDto,
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        $groupDescription, $groupCode
    )
    {
        $this->isNationalAverageAvailable = $nationalComponentStatisticsDto->getReportStatus()->getIsCompleted();
        $motTestingPerformanceDto = $breakdownDto->getGroupPerformance();
        $this->setTestCount($motTestingPerformanceDto->getTotal());
        $this->setAverageTestDuration(
            !is_null($motTestingPerformanceDto->getAverageTime())
                ? $motTestingPerformanceDto->getAverageTime()->getTotalMinutes()
                : static::TEXT_EMPTY
        );
        $this->setFailurePercentage($this->numberFormat($motTestingPerformanceDto->getPercentageFailed()));
        $this->setGroupDescription($groupDescription);
        $this->setGroupCode($groupCode);
        $this->averageVehicleAge = $this->determineVehicleAge($motTestingPerformanceDto);

        $this->componentRows = $this->createComponentRows(
            $breakdownDto->getComponents(),
            $nationalComponentStatisticsDto->getComponents()
        );
    }

    public function isNationalAverageAvailable()
    {
        return $this->isNationalAverageAvailable;
    }

    public function getTestCount()
    {
        return $this->getNotEmptyText($this->testCount, 0);
    }

    public function setTestCount($testCount)
    {
        $this->testCount = $testCount;

        return $this;
    }

    public function getAverageTestDuration()
    {
        return $this->getNotEmptyText($this->averageTestDuration);
    }

    public function setAverageTestDuration($averageTestDuration)
    {
        $this->averageTestDuration = $averageTestDuration;

        return $this;
    }

    public function getFailurePercentage()
    {
        return $this->getNotEmptyText($this->failurePercentage, static::TEXT_EMPTY, '%');
    }

    public function setFailurePercentage($failurePercentage)
    {
        $this->failurePercentage = $failurePercentage;

        return $this;
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
     * @return ComponentStatisticsRow[]
     */
    private function createComponentRows($userComponents, $nationalComponents)
    {
        $rows = [];

        foreach ($userComponents as $userComponent) {
            $nationalComponent = $this->getTesterDataByComponentId($userComponent->getId(), $nationalComponents);
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
                );
        }

        return $rows;
    }


    /**
     * @return string
     */
    public function getGroupName()
    {
        return 'Group ' . $this->groupCode;
    }

    /**
     * @return mixed
     */
    public function getAverageVehicleAge()
    {
        return $this->averageVehicleAge;
    }

    /**
     * @param mixed $averageVehicleAge
     * @return ComponentStatisticsTable
     */
    public function setAverageVehicleAge($averageVehicleAge)
    {
        $this->averageVehicleAge = $averageVehicleAge;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupDescription()
    {
        return $this->groupDescription;
    }

    /**
     * @param string $groupDescription
     */
    public function setGroupDescription($groupDescription)
    {
        $this->groupDescription = $groupDescription;
    }

    /**
     * @return string
     */
    public function getGroupCode()
    {
        return $this->groupCode;
    }

    /**
     * @param string $groupCode
     */
    public function setGroupCode($groupCode)
    {
        $this->groupCode = $groupCode;
    }

    /**
     * @param int $componentId
     * @param ComponentDto[] $userComponents
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
     * @return string
     */
    protected function getNotEmptyText($value, $defaultValue = self::TEXT_EMPTY, $appendIfNotEmpty = '')
    {
        if (!is_null($value)) {
            return $value . $appendIfNotEmpty;
        } else {
            return $defaultValue;
        }
    }

    /**
     * @param MotTestingPerformanceDto $motTestingPerformanceDto
     * @return int|string
     */
    protected function determineVehicleAge($motTestingPerformanceDto)
    {
        $age = self::TEXT_NOT_AVAILABLE;

        if ($motTestingPerformanceDto->getIsAverageVehicleAgeAvailable()) {
            $age = VehicleAgeFormatter::calculateVehicleAge($motTestingPerformanceDto->getAverageVehicleAgeInMonths());
            $age = $age . ' ' . VehicleAgeFormatter::getYearSuffix($age);
        }

        return $age;
    }

    /**
     * @param float $number
     * @return int|string
     */
    protected function numberFormat($number)
    {
        return is_numeric($number) ? round($number) : $number;
    }
}