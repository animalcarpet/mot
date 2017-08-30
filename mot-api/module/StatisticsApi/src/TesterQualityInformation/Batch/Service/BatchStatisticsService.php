<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task\AbstractBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task\NationalComponentBreakdownBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator\DateRangeValidator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Dto\Statistics\GeneratedReportDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;

class BatchStatisticsService
{
    private $s3Service;
    private $dateTimeHolder;
    /**
     * @var NationalComponentStatisticsService
     */
    private $nationalComponentBreakdownStatisticsService;

    public function __construct(
        KeyValueStorageInterface $s3Service,
        DateTimeHolderInterface $dateTimeHolder,
        NationalComponentStatisticsService $nationalComponentBreakdownStatisticsService
    ) {
        $this->s3Service = $s3Service;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->nationalComponentBreakdownStatisticsService = $nationalComponentBreakdownStatisticsService;
    }

    public function generateReports()
    {
        $componentBreakdownTasksGroupA = $this->getTasksForComponentBreakdown(DateRangeValidator::DATE_RANGE, VehicleClassGroupCode::BIKES);
        $componentBreakdownTasksGroupB = $this->getTasksForComponentBreakdown(DateRangeValidator::DATE_RANGE, VehicleClassGroupCode::CARS_ETC);

        $allTasks = array_merge($componentBreakdownTasksGroupA, $componentBreakdownTasksGroupB);

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

    /**
     * @param int[] $monthRanges
     * @param $vehicleGroup
     *
     * @return AbstractBatchTask[]
     */
    private function getTasksForComponentBreakdown(array $monthRanges, $vehicleGroup)
    {
        $keyGenerator = new S3KeyGenerator();

        $folder = $keyGenerator->getComponentBreakdownFolderForGroup($vehicleGroup);

        $existingReports = $this->s3Service->listKeys($folder);

        $year = $this->dateTimeHolder->getCurrentDate()->format('Y');
        $month = $this->dateTimeHolder->getCurrentDate()->format('n');

        $missingMonths = ArrayUtils::filter($monthRanges, function (int $monthRange)
            use ($existingReports, $keyGenerator, $vehicleGroup, $year, $month) {
                $expectedReport = $keyGenerator->generateForComponentBreakdownStatistics(
                    $year,
                    $month,
                    $vehicleGroup,
                    $monthRange
                );

            return !in_array($expectedReport, $existingReports);
        });

        $tasks = ArrayUtils::map($missingMonths, function (int $monthRange) use ($vehicleGroup) {
            return new NationalComponentBreakdownBatchTask($vehicleGroup, $monthRange, $this->nationalComponentBreakdownStatisticsService);
        });

        return $tasks;
    }
}
