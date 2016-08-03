<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Report\NationalStatisticsReportGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Repository\NationalStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\NationalTesterPerformanceStatisticsStorage;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\ParameterCheck\StatisticsParameterCheck;
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

    public function __construct(
        NationalStatisticsRepository $nationalStatisticsRepository,
        NationalTesterPerformanceStatisticsStorage $storage,
        DateTimeHolderInterface $dateTimeHolder,
        TimeSpan $timeoutPeriod
    )
    {
        $this->repository = $nationalStatisticsRepository;
        $this->storage = $storage;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->timeoutPeriod = $timeoutPeriod;
    }

    /**
     * @param $year
     * @param $month
     * @return NationalPerformanceReportDto
     * @throws NotFoundException
     */
    public function get($year, $month)
    {
        $this->validateParams($year, $month);

        $generator = new NationalStatisticsReportGenerator(
            $this->repository,
            $this->storage,
            $this->dateTimeHolder,
            $this->timeoutPeriod,
            $year,
            $month
        );

        return $generator->get();
    }

    private function validateParams($year, $month)
    {
        $validator = new StatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month)) {
            throw new NotFoundException("National Statistics");
        }
    }
}
