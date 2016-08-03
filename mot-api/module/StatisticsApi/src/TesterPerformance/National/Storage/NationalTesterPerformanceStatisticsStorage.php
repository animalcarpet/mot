<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\QueryResult\NationalStatisticsResult;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;

class NationalTesterPerformanceStatisticsStorage
{
    private $storage;
    private $keyGenerator;

    public function __construct(
        KeyValueStorageInterface $statisticsStorage
    )
    {
        $this->storage = $statisticsStorage;
        $this->keyGenerator = new S3KeyGenerator();
    }

    /**
     * @param $year
     * @param $month
     * @return NationalStatisticsResult
     */
    public function get($year, $month)
    {
        $key = $this->keyGenerator->generateForNationalTesterStatistics($year, $month);

        return $this->storage->getAsDto($key, NationalPerformanceReportDto::class);
    }

    public function store($year, $month, NationalPerformanceReportDto $data)
    {
        $key = $this->keyGenerator->generateForNationalTesterStatistics($year, $month);

        $this->storage->storeDto($key, $data);
    }
}
