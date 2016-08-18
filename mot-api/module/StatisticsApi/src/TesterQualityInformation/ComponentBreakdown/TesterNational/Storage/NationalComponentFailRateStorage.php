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
    )
    {
        $this->storage = $statisticsStorage;
        $this->keyGenerator = new S3KeyGenerator();
    }

    /**
     * @param int $year
     * @param int $month
     * @param string $group
     * @return NationalComponentStatisticsDto
     */
    public function get($year, $month, $group)
    {
        $key = $this->keyGenerator->generateForComponentBreakdownStatistics($year, $month, $group);

        return $this->storage->getAsDto($key, NationalComponentStatisticsDto::class);
    }

    /**
     * @param $year
     * @param $month
     * @param $group
     * @param NationalComponentStatisticsDto $dto
     */
    public function store($year, $month, $group, NationalComponentStatisticsDto $dto)
    {
        $key = $this->keyGenerator->generateForComponentBreakdownStatistics($year, $month, $group);

        $this->storage->storeDto($key, $dto);
    }
}
