<?php

use DvsaMotApi\Controller\MotTestController;
use DvsaMotApi\Controller\VehicleHistoryController;
use VehicleApi\Controller\MysteryShopperVehicleController;
use VehicleApi\Controller\VehicleCertificateExpiryController;
use VehicleApi\Controller\VehicleController;
use VehicleApi\Controller\VehicleDvlaController;
use VehicleApi\Controller\VehicleRetestEligibilityController;
use VehicleApi\Controller\VehicleSearchController;

return [
    'controllers' => include 'controllers.config.php',
    'router' => [
        'routes' => [
            'vehicle-search' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/vehicle-search',
                    'defaults' => [
                        'controller' => VehicleSearchController::class,
                    ],
                ],
            ],
            'vehicle-new' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/vehicle/:id',
                    'defaults' => [
                        'controller' => VehicleController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'test-in-progress-check' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/test-in-progress-check',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action' => 'isTestInProgress',
                            ],
                        ],
                    ],
                    'test-expiry-check' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/test-expiry-check[/:isDvla]',
                            'defaults' => [
                                'controller' => VehicleCertificateExpiryController::class,
                            ],
                            'constraints' => [
                                'isDvla' => '[0-1]',
                            ],
                        ],
                    ],
                    'test-history' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/test-history',
                            'defaults' => [
                                'controller' => VehicleHistoryController::class,
                            ],
                        ],
                    ],
                    'retest-eligibility-check' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/retest-eligibility-check/[:siteId]',
                            'defaults' => [
                                'controller' => VehicleRetestEligibilityController::class,
                            ],
                        ],
                    ],
                    'mystery-shopper-campaign' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/mystery-shopper-campaign',
                            'defaults' => [
                                'controller' => MysteryShopperVehicleController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'delete' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/:incognitoVehicleId',
                                    'defaults' => [
                                        'controller' => MysteryShopperVehicleController::class,
                                    ],
                                ],
                            ],
                            'current' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/current',
                                    'defaults' => [
                                        'controller' => MysteryShopperVehicleController::class,
                                    ],
                                ],
                            ],
                            'extend' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/extend',
                                    'defaults' => [
                                        'controller' => MysteryShopperVehicleController::class,
                                    ],
                                ],
                            ],
                            'list' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'controller' => MysteryShopperVehicleController::class,
                                        'action' => 'list',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'vehicle-new-list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/vehicle/list',
                    'defaults' => [
                        'controller' => VehicleController::class,
                    ],
                ],
            ],
            'vehicle-dvla' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/vehicle-dvla/:id',
                    'defaults' => [
                        'controller' => VehicleDvlaController::class,
                    ],
                ],
            ],
        ],
    ],
];
