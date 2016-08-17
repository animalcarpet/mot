<?php

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\AuthorisedExaminer\Controller\AuthorisedExaminerStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Controller\NationalBatchStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Controller\NationalComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Controller\NationalStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Controller\SiteStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Controller\TesterAtSiteComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Controller\TesterComponentStatisticsController;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Controller\TesterAggregatedStatisticsController;

return [
    'router' => [
        'routes' => [
            'national-tester-statistics'       => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/tester-performance/national/:year/:month',
                    'constraints' => [
                        'year'  => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => NationalStatisticsController::class,
                    ],
                ],
            ],
            'site-tester-statistics'           => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/tester-performance/site/:id/:year/:month',
                    'constraints' => [
                        'id'    => '[0-9]+',
                        'year'  => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults'    => [
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
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/tester-performance/national/batch',
                    'constraints' => [

                    ],
                    'defaults'    => [
                        'controller' => NationalBatchStatisticsController::class,
                    ],
                ],
            ],
            'tester-at-site-component-fail-rate'              => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/component-fail-rate/site/:siteId/tester/:testerId/group/:group/:year/:month',
                    'constraints' => [
                        'siteId'   => '[0-9]+',
                        'testerId' => '[0-9]+',
                        'group'    => 'A|B',
                        'year'     => '[0-9]+',
                        'month'    => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => TesterAtSiteComponentStatisticsController::class,
                    ]
                ]
            ],
            'tester-component-fail-rate'              => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/component-fail-rate/tester/:testerId/group/:group/:year/:month',
                    'constraints' => [
                        'testerId' => '[0-9]+',
                        'group'    => 'A|B',
                        'year'     => '[0-9]+',
                        'month'    => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => TesterComponentStatisticsController::class,
                    ]
                ]
            ],
            'national-component-fail-rate'     => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/component-fail-rate/national/group/:id/:year/:month',
                    'constraints' => [
                        'id'    => 'A|B',
                        'year'  => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => NationalComponentStatisticsController::class,
                    ]
                ]
            ],
            'tester-aggregated-statistics'           => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/statistic/tester-performance/tester/:id/:year/:month',
                    'constraints' => [
                        'id'    => '[0-9]+',
                        'year'  => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => TesterAggregatedStatisticsController::class,
                    ],
                ],
            ],
        ],
    ],
];
