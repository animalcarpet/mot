<?php

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\BatchPersonStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\NationalBatchStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\NationalBatchStatisticsForMonthController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Controller\MultipleTestersAtSiteComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator\DateRangeValidator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Controller\SiteAverageComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Controller\TesterAtSiteComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Controller\NationalComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Controller\SiteManagerNotificationController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Controller\AuthorisedExaminerStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Controller\TesterAggregatedStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Controller\SiteStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Controller\TesterMultiSiteStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Controller\NationalStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Controller\AedmNotificationController;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\Factory\BatchPersonStatisticsControllerFactory;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;

return [
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            BatchPersonStatisticsController::class => BatchPersonStatisticsControllerFactory::class
        ],
    ],
    'router' => [
        'routes' => [
            'national-tester-statistics' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/national/:monthRange',
                    'constraints' => [
                        'monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
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
                        'monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
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
            'batch-national-tester-statistics-chosen-month-days' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/national/batch-for-month/:year/:month/:day',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                        'day' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => NationalBatchStatisticsForMonthController::class,
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
            'batch-test-person-statistics-generation-for-month' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/batch/test-counts-for-month/:year/:month/:day',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                        'day' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => BatchPersonStatisticsController::class,
                        'action' => 'generateTestCountsForDate',
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
            'batch-rfr-person-statistics-generation-for-month' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-performance/batch/rfr-counts-for-month/:year/:month/:day',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                        'day' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => BatchPersonStatisticsController::class,
                        'action' => 'generateRfrCountsForDate',
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
                    'route' => '/statistic/component-fail-rate/site/:siteId/tester/:testerId/group/:group/monthRange/:monthRange',
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
            'multiple-testers-at-site-component-fail-rate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/component-fail-rate/site/:siteId/group/:group/monthRange/:monthRange',
                    'constraints' => [
                        'siteId' => '[0-9]+',
                        'group' => 'A|B',
                        'monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE)
                    ],
                    'defaults' => [
                        'controller' => MultipleTestersAtSiteComponentStatisticsController::class,
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
                        'monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
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
                        ':monthRange' => ArrayUtils::joinNonEmpty("|", DateRangeValidator::DATE_RANGE),
                    ],
                    'defaults' => [
                        'controller' => TesterMultiSiteStatisticsController::class,
                    ],
                ],
            ],
            'aedm-notification' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-quality-information/notification/aedm',
                    'defaults' => [
                        'controller' => AedmNotificationController::class,
                    ],
                ],
            ],
            'sm-notification' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-quality-information/notification/sm',
                    'defaults' => [
                        'controller' => SiteManagerNotificationController::class,
                    ],
                ],
            ],
            'sm-notification-is-sent' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-quality-information/notification/sm/is-sent',
                    'defaults' => [
                        'controller' => SiteManagerNotificationController::class,
                        'action' => 'checkIfNotificationHasBeenSent',
                    ],
                ],
            ],
            'aedm-notification-is-sent' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/statistic/tester-quality-information/notification/aedm/is-sent',
                    'defaults' => [
                        'controller' => AedmNotificationController::class,
                        'action' => 'checkIfNotificationHasBeenSent',
                    ],
                ],
            ],
        ],
    ],
];
