<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck\GroupStatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Report\NationalComponentStatisticsReportGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Repository\NationalComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;

class NationalComponentStatisticsService implements AutoWireableInterface
{
    private $repository;
    private $storage;
    private $dateTimeHolder;

    public function __construct(
        NationalComponentFailRateStorage $storage,
        NationalComponentStatisticsRepository $componentStatisticsRepository,
        DateTimeHolder $dateTimeHolder
    )
    {
        $this->repository = $componentStatisticsRepository;
        $this->storage = $storage;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function get($year, $month, $group)
    {
        $this->validate($year, $month, $group);

        $generator = new NationalComponentStatisticsReportGenerator(
            $this->storage,
            $this->repository,
            $this->dateTimeHolder,
            new TimeSpan(0, 1, 0, 0),
            $year,
            $month,
            $group
        );

        return $generator->get();
    }

    private function validate($year, $month, $group)
    {
        $validator = new GroupStatisticsParameterCheck();
        if (!$validator->isValid($year, $month, $group)) {
            throw new NotFoundException("National Component Statistics");
        }
    }
}
