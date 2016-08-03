<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Task\AbstractBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Task\NationalComponentBreakdownBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Task\NationalTesterStatisticsBatchTask;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\Month;
use DvsaCommon\Dto\Statistics\GeneratedReportDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;

class BatchStatisticsService
{
    private $s3Service;
    const NUMBER_OF_PAST_MONTHS_TO_GENERATE = 12;
    private $dateTimeHolder;
    private $nationalStatisticsService;
    /**
     * @var NationalComponentStatisticsService
     */
    private $nationalComponentBreakdownStatisticsService;

    function __construct(
        KeyValueStorageInterface $s3Service,
        DateTimeHolderInterface $dateTimeHolder,
        NationalStatisticsService $nationalStatisticsService,
        NationalComponentStatisticsService $nationalComponentBreakdownStatisticsService
    )
    {
        $this->s3Service = $s3Service;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->nationalStatisticsService = $nationalStatisticsService;
        $this->nationalComponentBreakdownStatisticsService = $nationalComponentBreakdownStatisticsService;
    }

    public function generateReports()
    {
        $testerPerformanceTasks = $this->getTasksForTesterPerformance($this->getMonths());
        $componentBreakdownTasksGroupA = $this->getTasksForComponentBreakdown($this->getMonths(), VehicleClassGroupCode::BIKES);
        $componentBreakdownTasksGroupB = $this->getTasksForComponentBreakdown($this->getMonths(), VehicleClassGroupCode::CARS_ETC);

        /** @var AbstractBatchTask[] $allTasks */
        $allTasks = array_merge($testerPerformanceTasks, $componentBreakdownTasksGroupA, $componentBreakdownTasksGroupB);

        $allTasks = $this->sortTaskByMothDesc($allTasks);

        foreach ($allTasks as $task) {
            $task->execute();
        }

        $dtos = ArrayUtils::map($allTasks, function (AbstractBatchTask $task) {
            return (new GeneratedReportDto())->setName($task->getName());
        });

        return $dtos;
    }

    /**
     * @param AbstractBatchTask[] $tasks
     * @return AbstractBatchTask[]
     */
    public function sortTaskByMothDesc(array $tasks)
    {
        $taskComparator = function (AbstractBatchTask $taskA, AbstractBatchTask $taskB) {
            if ($taskA->getMonth()->equals($taskB->getMonth())) {
                return 0;
            }
            if ($taskA->getMonth()->greaterThan($taskB->getMonth())) {
                return -1;
            }
            return 1;
        };

        usort($tasks, $taskComparator);

        return $tasks;
    }

    /**
     * @param Month[] $months
     * @return AbstractBatchTask[]
     */
    private function getTasksForTesterPerformance(array $months)
    {
        $keyGenerator = new S3KeyGenerator();

        $existingReports = $this->s3Service->listKeys(S3KeyGenerator::NATIONAL_TESTER_STATISTICS_FOLDER);

        $missingMonths = ArrayUtils::filter($months, function (Month $month) use ($existingReports, $keyGenerator) {
            $expectedReport = $keyGenerator->generateForNationalTesterStatistics($month->getYear(), $month->getMonth());
            return !in_array($expectedReport, $existingReports);
        });

        $tasks = ArrayUtils::map($missingMonths, function (Month $month) {
            return new NationalTesterStatisticsBatchTask($month, $this->nationalStatisticsService);
        });

        return $tasks;
    }

    /**
     * @param Month[] $months
     * @param $vehicleGroup
     * @return AbstractBatchTask[]
     */
    private function getTasksForComponentBreakdown(array $months, $vehicleGroup)
    {
        $keyGenerator = new S3KeyGenerator();

        $folder = $keyGenerator->getComponentBreakdownFolderForGroup($vehicleGroup);

        $existingReports = $this->s3Service->listKeys($folder);

        $missingMonths = ArrayUtils::filter($months, function (Month $month) use ($existingReports, $keyGenerator, $vehicleGroup) {
            $expectedReport = $keyGenerator->generateForComponentBreakdownStatistics($month->getYear(), $month->getMonth(), $vehicleGroup);
            return !in_array($expectedReport, $existingReports);
        });

        $tasks = ArrayUtils::map($missingMonths, function (Month $month) use ($vehicleGroup) {
            return new NationalComponentBreakdownBatchTask($vehicleGroup, $month, $this->nationalComponentBreakdownStatisticsService);
        });

        return $tasks;
    }

    public function getMonths()
    {
        $today = $this->dateTimeHolder->getCurrentDate();
        $year = $today->format('Y');
        $month = $today->format('m');

        $currentMonth = new Month($year, $month);

        $months = [];

        for ($i = 0; $i < self::NUMBER_OF_PAST_MONTHS_TO_GENERATE; $i++) {
            $previousMonth = $currentMonth->previous();
            $months[] = $previousMonth;
            $currentMonth = $previousMonth;
        }

        return $months;
    }
}
