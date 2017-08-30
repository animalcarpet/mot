<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service;

use DvsaCommon\Date\LastMonthsDateRange;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Report\NationalStatisticsReportGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Repository\NationalStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\TimeSpan;
use DvsaCommonApi\Service\Exception\NotFoundException;

class NationalStatisticsService
{
    private $repository;
    private $storage;
    private $dateTimeHolder;
    private $timeoutPeriod;
    /**
     * @var LastMonthsDateRange
     */
    private $lastMonthsDateRange;

    public function __construct(
        NationalStatisticsRepository $nationalStatisticsRepository,
        NationalTesterPerformanceStatisticsStorage $storage,
        DateTimeHolderInterface $dateTimeHolder,
        TimeSpan $timeoutPeriod,
        LastMonthsDateRange $lastMonthsDateRange
    ) {
        $this->repository = $nationalStatisticsRepository;
        $this->storage = $storage;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->timeoutPeriod = $timeoutPeriod;
        $this->lastMonthsDateRange = $lastMonthsDateRange;
    }

    /**
     * @param $monthRange
     * @return NationalPerformanceReportDto
     * @throws NotFoundException
     */
    public function get(int $monthRange)
    {
        $generator = new NationalStatisticsReportGenerator(
            $this->repository,
            $this->storage,
            $this->dateTimeHolder,
            $this->timeoutPeriod,
            $this->lastMonthsDateRange->setNumberOfMonths($monthRange)
        );

        /** @var NationalPerformanceReportDto $reportDto */
        $reportDto = $generator->get();

        return $reportDto;
    }
}
