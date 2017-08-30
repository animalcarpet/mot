<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage\S3KeyGenerator;
use DvsaCommon\ApiClient\Statistics\Common\ReportDtoInterface;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;

class NationalTesterPerformanceStatisticsStorage
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
     * @param int $year
     * @param int $month
     * @param int $numberOfMonths
     * @return ReportDtoInterface
     */
    public function get(int $year, int $month, int $numberOfMonths)
    {
        $key = $this->keyGenerator->generateForNationalTesterStatistics($year, $month, $numberOfMonths);

        /** @var ReportDtoInterface $reportDto */
        $reportDto = $this->storage->getAsDto($key, NationalPerformanceReportDto::class);

        return $reportDto;
    }

    public function store(int $year, int $month, int $monthRange, NationalPerformanceReportDto $data)
    {
        $key = $this->keyGenerator->generateForNationalTesterStatistics($year, $month, $monthRange);

        $this->storage->storeDto($key, $data);
    }
}
