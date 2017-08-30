<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task\AbstractBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Task\NationalTesterStatisticsBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Dto\Statistics\GeneratedReportDto;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;

class TesterPerformanceBatchStatisticsService
{
    private $s3Service;
    private $dateTimeHolder;
    private $nationalStatisticsService;

    public function __construct(
        KeyValueStorageInterface $s3Service,
        DateTimeHolderInterface $dateTimeHolder,
        NationalStatisticsService $nationalStatisticsService
    ) {
        $this->s3Service = $s3Service;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->nationalStatisticsService = $nationalStatisticsService;
    }

    /**
     * @return GeneratedReportDto[]
     */
    public function generateReports()
    {
        $testerPerformanceTasks = $this->getTasksForTesterPerformance(
            [LastMonthsDateRange::ONE_MONTH, LastMonthsDateRange::THREE_MONTHS]
        );

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
     *
     * @return AbstractBatchTask[]
     */
    private function getTasksForTesterPerformance(array $monthRanges)
    {
        $keyGenerator = new S3KeyGenerator();

        $existingReports = $this->s3Service->listKeys(S3KeyGenerator::NATIONAL_TESTER_STATISTICS_FOLDER);

        $missingMonths = ArrayUtils::filter($monthRanges, function (int $month) use ($existingReports, $keyGenerator) {
            $expectedReport = $keyGenerator->generateForNationalTesterStatistics(
                $this->dateTimeHolder->getCurrentDate()->format('Y'),
                $this->dateTimeHolder->getCurrentDate()->format('n'),
                $month
            );

            return !in_array($expectedReport, $existingReports);
        });

        $tasks = ArrayUtils::map($missingMonths, function (int $monthRange) {
            return new NationalTesterStatisticsBatchTask($monthRange, $this->nationalStatisticsService);
        });

        return $tasks;
    }
}