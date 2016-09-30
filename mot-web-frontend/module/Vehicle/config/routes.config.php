<?php

use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use Vehicle\Controller\VehicleController;
use Vehicle\UpdateVehicleProperty\Controller\UpdateVehiclePropertyController;

return [
    'vehicle' => [
        'type' => 'segment',
        'options' => [
            'route' => '/vehicle',
            'defaults' => [
                'controller' => VehicleController::class,
                'action' => 'search',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'detail' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:id',
                    'constraints' => [
                        // 'vehicleId' => '[0-9]+',    // legacy mode: vehicleIds are integers
                        'vehicleId' => '[0-9a-zA-Z-_]+', // new mode: vehicleIds are obfuscated
                    ],
                    'defaults' => [
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'history' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/history',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByVehicle',
                            ],
                        ],
                    ],
                    'change' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'engine' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/engine',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editEngine',
                                    ],
                                ],
                            ],
                            'country-of-registration' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/country-of-registration',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editCountry',
                                    ],
                                ],
                            ],
                            'class' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/class',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editClass',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'search' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/search',
                    'defaults' => [
                        'action' => 'search',
                    ],
                ],
            ],
            'result' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/result',
                    'defaults' => [
                        'action' => 'result',
                    ],
                ],
            ],
        ],
    ],
];
