<?php

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Factory\Storage\S3\TqiStatisticsStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Factory\Storage\NationalComponentFailRateStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Batch\Factory\Service\BatchStatisticsServiceFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Factory\Storage\SiteTesterPerformanceStatisticsStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Storage\SiteTesterPerformanceStatisticsStorage;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Factory\Service\NationalStatisticsServiceFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Factory\Storage\NationalTesterPerformanceStatisticsStorageFactory;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage;

return [
    'factories' => [
        'TqiStore' => TqiStatisticsStorageFactory::class,
        NationalTesterPerformanceStatisticsStorage::class => NationalTesterPerformanceStatisticsStorageFactory::class,
        SiteTesterPerformanceStatisticsStorage::class => SiteTesterPerformanceStatisticsStorageFactory::class,
        NationalComponentFailRateStorage::class => NationalComponentFailRateStorageFactory::class,
        BatchStatisticsService::class => BatchStatisticsServiceFactory::class,
        NationalStatisticsService::class => NationalStatisticsServiceFactory::class,
    ],
    'invokables' => [

    ],
];
