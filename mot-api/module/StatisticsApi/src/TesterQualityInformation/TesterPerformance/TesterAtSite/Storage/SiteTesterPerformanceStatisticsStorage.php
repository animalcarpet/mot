<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;

class SiteTesterPerformanceStatisticsStorage
{
    const PER_SITE_STATS_FOLDER = 'per-site-stats';
    const PREFIX_FOR_SITE_ID = '%s/%s';

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
     * @param $siteId
     * @param $year
     * @param $month
     * @return TesterPerformanceResult[]
     */
    public function get($siteId, $year, $month)
    {
        $key = $this->keyGenerator->generateForSiteTesterStatistics($siteId, $year, $month);

        /** @var TesterPerformanceResult[] $dbResult */
        $dbResult =  $this->storage->getAsDto($key, TesterPerformanceResult::class);

        return $dbResult;
    }

    /**
     * @param $siteId
     * @param $year
     * @param $month
     * @param \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult[] $data
     * @throw S3Exception
     */
    public function store($siteId, $year, $month, array $data)
    {
        $key = $this->keyGenerator->generateForSiteTesterStatistics($siteId, $year, $month);

        $this->storage->storeDto($key, $data);
    }
}
