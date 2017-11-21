<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task\AbstractBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Task\NationalTesterStatisticsBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\DateRange;
use DvsaCommon\Date\DateRangeInterface;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\Month;
use DvsaCommon\Dto\Statistics\GeneratedReportDto;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaFeature\FeatureToggles;

class TesterPerformanceBatchStatisticsService extends AbstractBatchStatisticsService
{
    private $s3Service;
    private $dateTimeHolder;
    private $nationalStatisticsService;
    private $featureToggles;

    public function __construct(
        KeyValueStorageInterface $s3Service,
        DateTimeHolderInterface $dateTimeHolder,
        NationalStatisticsService $nationalStatisticsService,
        FeatureToggles $featureToggles
    ) {
        parent::__construct($dateTimeHolder, $featureToggles);
        $this->s3Service = $s3Service;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->nationalStatisticsService = $nationalStatisticsService;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @return GeneratedReportDto[]
     */
    public function generateReports()
    {
        $currentDate = $this->dateTimeHolder->getCurrentDate();
        $year = $currentDate->format("Y");
        $month = $currentDate->format("n");

        $monthRanges = $this->getMonthRanges();

        $testerPerformanceTasks = $this->getTasksForTesterPerformance($monthRanges, $year, $month);

        return $this->executeTasks($testerPerformanceTasks);
    }

    public function generateReportsForDate(int $year, int $month, int $day)
    {
        $monthRange = new Month($year, $month, $day);
        $this->validateDate($monthRange);

        $targetedReportMonth = $monthRange->next()->getMonth(); // e.g. stats for 1st-20th May should appear as a report for June

        $statisticsDateRanges = $this->getMonthRangesForDate($monthRange);

        /** @var NationalTesterStatisticsBatchTask[] $testerPerformanceTasks */
        $testerPerformanceTasks = $this->getTasksForTesterPerformanceForDate($year, $targetedReportMonth, $statisticsDateRanges);

        return $this->executeTasks($testerPerformanceTasks);
    }

    /**
     * @param NationalTesterStatisticsBatchTask[] $testerPerformanceTasks
     * @return GeneratedReportDto[]
     */
    private function executeTasks(array $testerPerformanceTasks)
    {
        foreach ($testerPerformanceTasks as $task) {
            $task->execute();
        }

        $dtos = ArrayUtils::map($testerPerformanceTasks, function (NationalTesterStatisticsBatchTask $task) {
            return (new GeneratedReportDto())->setName($task->getName());
        });

        return $dtos;
    }

    /**
     * @param int[] $monthRanges
     * @param int $year
     * @param int month
     * @return AbstractBatchTask[]
     */
    private function getTasksForTesterPerformance(array $monthRanges, int $year, int $month)
    {
        $tasks = [];

        foreach ($monthRanges as $dateRangeInMonths) {
            $dateRange = (new LastMonthsDateRange($this->dateTimeHolder))->setNumberOfMonths($dateRangeInMonths);
            if($this->isReportGenerated($year, $month, $dateRange) === false) {
                $tasks[] = new NationalTesterStatisticsBatchTask($this->nationalStatisticsService, $year, $month, $dateRange);
            }
        }

        return $tasks;
    }

    /**
     * @param int $year
     * @param int $month
     * @param DateRange[] $dateRanges
     * @return AbstractBatchTask[]
     */
    private function getTasksForTesterPerformanceForDate(int $year, int $month, array $dateRanges)
    {
        $tasks = [];

        foreach ($dateRanges as $dateRange) {
            if ($this->isReportGenerated($year, $month, $dateRange) === false) {
                $tasks[] = new NationalTesterStatisticsBatchTask($this->nationalStatisticsService, $year, $month, $dateRange);
            }
        }

        return $tasks;
    }

    private function isReportGenerated($year, $month, DateRangeInterface $dateRange)
    {
        $existingReports = $this->s3Service->listKeys(S3KeyGenerator::NATIONAL_TESTER_STATISTICS_FOLDER);
        $keyGenerator = new S3KeyGenerator();
        $expectedReport = $keyGenerator->generateForNationalTesterStatistics(
            $year,
            $month,
            $dateRange->getNumberOfMonths()
        );
        return in_array($expectedReport, $existingReports);
    }
}