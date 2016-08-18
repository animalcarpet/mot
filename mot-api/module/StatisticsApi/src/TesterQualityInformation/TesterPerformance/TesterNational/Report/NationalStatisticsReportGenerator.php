<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Report;

use Dvsa\Mot\Api\StatisticsApi\ReportGeneration\AbstractReportGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\QueryResult\NationalStatisticsResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Repository\NationalStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage;
use DvsaCommon\ApiClient\Statistics\Common\ReportDtoInterface;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\TimeSpan;

class NationalStatisticsReportGenerator extends AbstractReportGenerator
{
    private $repository;
    private $storage;
    private $year;
    private $month;

    public function __construct(
        NationalStatisticsRepository $nationalStatisticsRepository,
        NationalTesterPerformanceStatisticsStorage $storage,
        DateTimeHolderInterface $dateTimeHolder,
        TimeSpan $timeoutPeriod,
        $year,
        $month
    )
    {
        parent::__construct($dateTimeHolder, $timeoutPeriod);
        $this->repository = $nationalStatisticsRepository;
        $this->storage = $storage;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return ReportDtoInterface
     */
    protected function getFromStorage()
    {
        return $this->storage->get($this->year, $this->month);
    }

    protected function generateReport()
    {
        $statistics = $this->repository->getStatistics($this->year, $this->month);

        $report = $this->createDtoFromDbResult($statistics, $this->year, $this->month);

        return $report;
    }

    protected function storeReport($report)
    {
        $this->storage->store($this->year, $this->month, $report);
    }

    /**
     * @return ReportDtoInterface
     */
    function createEmptyReport()
    {
        return new NationalPerformanceReportDto();
    }

    protected function returnInProgressReportDto()
    {
        $dto = new NationalPerformanceReportDto();
        $dto->getReportStatus()->setIsCompleted(false);

        return $dto;
    }

    private function createDtoFromDbResult(NationalStatisticsResult $report, $year, $month)
    {
        $dto = new NationalPerformanceReportDto();
        $dto->setYear($year);
        $dto->setMonth($month);

        $groupADto = $this->createGroupADto($report);
        $groupBDto = $this->createGroupBDto($report);

        $dto->setGroupA($groupADto);
        $dto->setGroupB($groupBDto);

        $dto->getReportStatus()->setIsCompleted(true);

        return $dto;
    }

    private function createGroupADto(NationalStatisticsResult $statistics)
    {
        return $this->createGroupDto(
            $statistics->getGroupATotal(),
            $statistics->getGroupAFailed(),
            $statistics->getNumberOfGroupATesters(),
            $statistics->getGroupACumulativeTestTime(),
            $statistics->getGroupAAverageVehicleAgeInMonths(),
            $statistics->getIsGroupAAverageVehicleAgeAvailable()
        );
    }

    private function createGroupBDto(NationalStatisticsResult $statistics)
    {
        return $this->createGroupDto(
            $statistics->getGroupBTotal(),
            $statistics->getGroupBFailed(),
            $statistics->getNumberOfGroupBTesters(),
            $statistics->getGroupBCumulativeTestTime(),
            $statistics->getGroupBAverageVehicleAgeInMonths(),
            $statistics->getIsGroupBAverageVehicleAgeAvailable()
        );
    }

    private function createGroupDto(
        $totalTests,
        $failedTests,
        $numberOfTesters,
        $cumulativeTestTime,
        $averageVehicleAge,
        $isAverageAgeAvailable
    )
    {
        $totalAverage = $this->calculateAverageCount($totalTests, $numberOfTesters);
        $averageTime = $this->getAverageTime($cumulativeTestTime, $totalTests);
        $failedPercentage = $this->calculatePercentage($failedTests, $totalTests);

        $motTestingPerformanceDto = new MotTestingPerformanceDto();

        $motTestingPerformanceDto
            ->setTotal($totalAverage)
            ->setAverageTime($averageTime)
            ->setPercentageFailed($failedPercentage)
            ->setIsAverageVehicleAgeAvailable($isAverageAgeAvailable)
            ->setAverageVehicleAgeInMonths($averageVehicleAge);

        return $motTestingPerformanceDto;
    }

    private function calculateAverageCount($total, $numberOfTesters)
    {
        return $numberOfTesters == 0 ? 0 : floor($total / $numberOfTesters);
    }

    private function calculatePercentage($failed, $total)
    {
        return $total == 0 ? 0 : $failed / $total * 100;
    }

    private function getAverageTime($cumulativeTestTime, $totalTests)
    {
        $averageSeconds = $totalTests > 0
            ? floor($cumulativeTestTime / $totalTests)
            : 0;

        return new TimeSpan(0, 0, 0, $averageSeconds);
    }
}
