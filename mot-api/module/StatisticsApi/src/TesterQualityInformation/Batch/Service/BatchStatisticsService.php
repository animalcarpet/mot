<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task\NationalComponentBreakdownBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use DvsaCommon\Date\DateRange;
use DvsaCommon\Date\DateRangeInterface;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\Month;
use DvsaCommon\Dto\Statistics\GeneratedReportDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaFeature\FeatureToggles;

class BatchStatisticsService extends AbstractBatchStatisticsService
{
    private $s3Service;
    private $dateTimeHolder;
    private $featureToggles;
    /**
     * @var NationalComponentStatisticsService
     */
    private $nationalComponentBreakdownStatisticsService;

    public function __construct(
        KeyValueStorageInterface $s3Service,
        DateTimeHolderInterface $dateTimeHolder,
        NationalComponentStatisticsService $nationalComponentBreakdownStatisticsService,
        FeatureToggles $featureToggles
    ) {
        parent::__construct($dateTimeHolder, $featureToggles);
        $this->s3Service = $s3Service;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->nationalComponentBreakdownStatisticsService = $nationalComponentBreakdownStatisticsService;
        $this->featureToggles = $featureToggles;
    }

    public function generateReports()
    {
        $currentDate = $this->dateTimeHolder->getCurrentDate();
        $year = $currentDate->format("Y");
        $month = $currentDate->format("n");

        $monthRanges = $this->getMonthRanges();

        $allTasks = array_merge(
            $this->getTasksForVehicleGroup(VehicleClassGroupCode::BIKES, $year, $month, $monthRanges),
            $this->getTasksForVehicleGroup(VehicleClassGroupCode::CARS_ETC, $year, $month, $monthRanges)
        );

        return $this->executeTasks($allTasks);
    }

    public function generateReportsForDate(int $year, int $month, int $day)
    {
        $monthRange = new Month($year, $month, $day);
        $this->validateDate($monthRange);

        $targetedReportMonth = $monthRange->next()->getMonth(); // e.g. stats for 1st-20th May should appear as a report for June

        $statisticsDateRanges = $this->getMonthRangesForDate($monthRange);

        $allTasks = array_merge(
            $this->getTasksForVehicleGroupForDate(VehicleClassGroupCode::BIKES, $year, $targetedReportMonth, $statisticsDateRanges),
            $this->getTasksForVehicleGroupForDate(VehicleClassGroupCode::CARS_ETC, $year, $targetedReportMonth, $statisticsDateRanges)
        );

        return $this->executeTasks($allTasks);
    }

    /**
     * @param string $vehicleGroup
     * @param string $year
     * @param string $month
     * @param int[] $monthRanges
     * @return array
     */
    private function getTasksForVehicleGroup(string $vehicleGroup, string $year, string $month, array $monthRanges)
    {
        $allTasks = [];
        foreach ($monthRanges as $dateRangeInMonths) {
            if ($this->isReportGenerated($dateRangeInMonths, $vehicleGroup, $year, $month) === false) {
                $dateRange = (new LastMonthsDateRange($this->dateTimeHolder))->setNumberOfMonths($dateRangeInMonths);
                $allTasks[] = $this->getTasksForComponentBreakdown($dateRange, $vehicleGroup, $year, $month);
            }
        }
        return $allTasks;
    }

    /**
     * @param string $vehicleGroup
     * @param string $year
     * @param string $month
     * @param DateRange[] $dateRanges
     * @return array
     */
    private function getTasksForVehicleGroupForDate(string $vehicleGroup, string $year, string $month, array $dateRanges)
    {
        $allTasks = [];
        foreach ($dateRanges as $dateRange) {
            if ($this->isReportGenerated($dateRange->getNumberOfMonths(), $vehicleGroup, $year, $month) === false) {
                $allTasks[] = $this->getTasksForComponentBreakdown($dateRange, $vehicleGroup, $year, $month);
            }
        }
        return $allTasks;
    }

    /**
     * @param $allTasks
     * @return GeneratedReportDto[]
     */
    private function executeTasks($allTasks)
    {
        /** @var NationalComponentBreakdownBatchTask[] $allTasks */
        $allTasks = NationalComponentBreakdownBatchTask::sortTaskByMonthRange($allTasks);

        foreach ($allTasks as $task) {
            $task->execute();
        }

        $dtos = ArrayUtils::map($allTasks, function (NationalComponentBreakdownBatchTask $task) {
            return (new GeneratedReportDto())->setName($task->getName());
        });

        return $dtos;
    }

    private function isReportGenerated(int $monthRange, string $vehicleGroup, $year, $month)
    {
        $keyGenerator = new S3KeyGenerator();

        $folder = $keyGenerator->getComponentBreakdownFolderForGroup($vehicleGroup);

        $existingReports = $this->s3Service->listKeys($folder);

        $expectedReport = $keyGenerator->generateForComponentBreakdownStatistics(
            $year,
            $month,
            $vehicleGroup,
            $monthRange
        );

        if (in_array($expectedReport, $existingReports)) {
            return true;
        }

        return false;
    }
    /**
     * @param DateRangeInterface $dateRange
     * @param string $vehicleGroup
     * @param int $reportYear
     * @param int $reportMonth
     * @return NationalComponentBreakdownBatchTask
     */
    private function getTasksForComponentBreakdown(DateRangeInterface $dateRange, string $vehicleGroup, int $reportYear, int $reportMonth)
    {
        $tasks = new NationalComponentBreakdownBatchTask($vehicleGroup, $dateRange, $this->nationalComponentBreakdownStatisticsService, $reportYear, $reportMonth);

        return $tasks;
    }
}
