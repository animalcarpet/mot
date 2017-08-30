<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;

class NationalComponentFailRateStorage
{
    private $storage;
    private $keyGenerator;

    public function __construct(
        KeyValueStorageInterface $statisticsStorage
    ) {
        $this->storage = $statisticsStorage;
        $this->keyGenerator = new S3KeyGenerator();
    }

    /**
     * @param int    $year
     * @param int    $month
     * @param int    $monthRange
     * @param string $group
     *
     * @return NationalComponentStatisticsDto
     */
    public function get(int $year, int $month, int $monthRange, string $group)
    {
        $key = $this->keyGenerator->generateForComponentBreakdownStatistics($year, $month, $group, $monthRange);

        return $this->storage->getAsDto($key, NationalComponentStatisticsDto::class);
    }

    /**
     * @param $year
     * @param $month
     * @param $monthRange
     * @param $group
     * @param NationalComponentStatisticsDto $dto
     */
    public function store($year, $month, $monthRange, $group, NationalComponentStatisticsDto $dto)
    {
        $key = $this->keyGenerator->generateForComponentBreakdownStatistics($year, $month, $group, $monthRange);

        $this->storage->storeDto($key, $dto);
    }
}
