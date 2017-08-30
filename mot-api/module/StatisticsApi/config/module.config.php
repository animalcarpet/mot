<?php

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\BatchPersonStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\NationalBatchStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator\DateRangeValidator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Controller\SiteAverageComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Controller\TesterAtSiteComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Controller\NationalComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Controller\AuthorisedExaminerStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Controller\TesterAggregatedStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Controller\SiteStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Controller\TesterMultiSiteStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Controller\NationalStatisticsController;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;

return [
    'router' => [
        'routes' => [
            'national-tester-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/national/:monthRange',
                    'constraints' => [
                        'monthRange' =>  ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => NationalStatisticsController::class,
                    ],
                ],
            ],
            'site-tester-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/site/:id/:monthRange',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'monthRange' =>  ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => SiteStatisticsController::class,
                    ],
                ],
            ],
            'authorised-examiner-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/authorised-examiner/:id',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => AuthorisedExaminerStatisticsController::class,
                        'page' => 1,
                    ],
                ],
            ],
            'batch-national-tester-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/national/batch',
                    'constraints' => [

                    ],
                    'defaults' => [
                        'controller' => NationalBatchStatisticsController::class,
                    ],
                ],
            ],
            'batch-test-person-statistics-generation' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/batch/test-counts',
                    'defaults' => [
                        'controller' => BatchPersonStatisticsController::class,
                        'action' => 'generateTestCounts',
                    ],
                ],
            ],
            'batch-rfr-person-statistics-generation' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/batch/rfr-counts',
                    'defaults' => [
                        'controller' => BatchPersonStatisticsController::class,
                        'action' => 'generateRfrCounts',
                    ],
                ],
            ],
            'site-average-component-fail-rate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/component-fail-rate/site-average/:siteId/group/:group/:monthRange',
                    'constraints' => [
                        'siteId' => '[0-9]+',
                        'group' => ArrayUtils::joinNonEmpty("|", [VehicleClassGroupCode::BIKES, VehicleClassGroupCode::CARS_ETC]),
                        'monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => SiteAverageComponentStatisticsController::class,
                    ],
                ],
            ],
            'tester-at-site-component-fail-rate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/component-fail-rate/site/:siteId/tester/:testerId/group/:group/:monthRange',
                    'constraints' => [
                        'siteId' => '[0-9]+',
                        'testerId' => '[0-9]+',
                        'group' => ArrayUtils::joinNonEmpty("|", [VehicleClassGroupCode::BIKES, VehicleClassGroupCode::CARS_ETC]),
                        'monthRange' =>  ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => TesterAtSiteComponentStatisticsController::class,
                    ],
                ],
            ],
            'national-component-fail-rate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/component-fail-rate/national/group/:id/:monthRange',
                    'constraints' => [
                        'id' => ArrayUtils::joinNonEmpty("|", [VehicleClassGroupCode::BIKES, VehicleClassGroupCode::CARS_ETC]),
                        'monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => NationalComponentStatisticsController::class,
                    ],
                ],
            ],
            'tester-aggregated-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/tester/:id/:monthRange',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'monthRange' =>  ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => TesterAggregatedStatisticsController::class,
                    ],
                ],
            ],
            'tester-multi-site-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/multi-site/:id/:monthRange',
                    'constraints' => [
                        'id' => '[0-9]+',
                        ':monthRange' =>  ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => TesterMultiSiteStatisticsController::class,
                    ],
                ],
            ],
        ],
    ],
];
