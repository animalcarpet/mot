<?php

use Dvsa\Mot\Api\StatisticsApi\Factory\Storage\S3\TqiStatisticsStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Service\BatchStatisticsServiceFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Service\NationalStatisticsServiceFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Storage\NationalComponentFailRateStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Storage\NationalTesterPerformanceStatisticsStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\BatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\NationalStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\NationalComponentFailRateStorage;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\NationalTesterPerformanceStatisticsStorage;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Factory\Storage\SiteTesterPerformanceStatisticsStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Storage\SiteTesterPerformanceStatisticsStorage;

return [
    'factories'  => [
        "TqiStore"                                        => TqiStatisticsStorageFactory::class,
        NationalTesterPerformanceStatisticsStorage::class => NationalTesterPerformanceStatisticsStorageFactory::class,
        SiteTesterPerformanceStatisticsStorage::class     => SiteTesterPerformanceStatisticsStorageFactory::class,
        NationalComponentFailRateStorage::class           => NationalComponentFailRateStorageFactory::class,
        BatchStatisticsService::class                     => BatchStatisticsServiceFactory::class,
        NationalStatisticsService::class => NationalStatisticsServiceFactory::class,
    ],
    'invokables' => [

    ]
];
