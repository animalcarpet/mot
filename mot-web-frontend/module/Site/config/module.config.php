<?php

use Site\Controller\VehicleTestingStationController;
use Site\Controller\RoleController;
use Site\Factory\Controller\RoleControllerFactory;
use Site\Controller\SiteTestingDailyScheduleController;
use Site\Factory\Controller\SiteSearchControllerFactory;

return [
    'router'       => [
        'routes' => [
            'vehicle-testing-station-search-for-person'  => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle-testing-station/:vehicleTestingStationId/search-for-person',
                    'defaults' => [
                        'controller' => RoleController::class,
                        'action'     => 'searchForPerson',
                    ],
                ],
            ],
            'vehicle-testing-station-list-user-roles'    => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:vehicleTestingStationId/:personId/list-roles',
                    'constraints' => [
                        'personId' => '[1-9]+[0-9]*',
                    ],
                    'defaults'    => [
                        'controller' => RoleController::class,
                        'action'     => 'listUserRoles',
                    ],
                ],
            ],
            'vehicle-testing-station-confirm-nomination' => [
                'type'    => 'segment',
                'options' => [
                    'route'    =>
                        '/vehicle-testing-station/:vehicleTestingStationId/:nomineeId/confirm-nomination/:roleCode',
                    'defaults' => [
                        'controller' => RoleController::class,
                        'action'     => 'confirmNomination',
                    ],
                ],
            ],
            'vehicle-testing-station'                    => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => VehicleTestingStationController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'vehicle-testing-station-edit'               => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/edit',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => VehicleTestingStationController::class,
                        'action'     => 'edit',
                    ],
                ],
            ],
            'vehicle-testing-station-update'             => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/contact-details',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => VehicleTestingStationController::class,
                        'action'     => 'contact-details',
                    ],
                ],
            ],
            'vehicle-testing-station-search'             => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle-testing-station/search',
                    'defaults' => [
                        'controller' => SiteSearchControllerFactory::class,
                        'action'     => 'search',
                    ],
                ],
            ],
            'vehicle-testing-station-result'             => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle-testing-station/result',
                    'defaults' => [
                        'controller' => SiteSearchControllerFactory::class,
                        'action'     => 'result',
                    ],
                ],
            ],
            'vehicle-testing-station-by-site'            => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/site/:sitenumber',
                    'constraints' => [
                        'sitenumber' => '[0-9a-zA-Z]+',
                    ],
                    'defaults'    => [
                        'controller' => VehicleTestingStationController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'site'                                       => [
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => [
                    'route' => '/vehicle-testing-station',
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'create'             => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/create',
                            'defaults' => [
                                'controller' => VehicleTestingStationController::class,
                                'action'     => 'create',
                            ],
                        ],
                    ],
                    'configure-brake-test-defaults' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:id/configure-brake-test-defaults',
                            'defaults' => [
                                'id'         => '[0-9]+',
                                'controller' => VehicleTestingStationController::class,
                                'action'     => 'configureBrakeTestDefaults'
                            ],
                        ],
                    ],
                    'remove-role'                   => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:siteId/remove-role/:positionId',
                            'defaults' => [
                                'siteId'     => '[0-9]+',
                                'positionId' => '[0-9]+',
                                'controller' => RoleController::class,
                                'action'     => 'remove'
                            ],
                        ],
                    ],
                    'edit-opening-hours'            => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:siteId/opening-hours/edit',
                            'defaults' => [
                                'siteId'     => '[0-9]+',
                                'controller' => SiteTestingDailyScheduleController::class,
                                'action'     => 'edit'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers'  => [
        'invokables' => [
            VehicleTestingStationController::class => VehicleTestingStationController::class,
            SiteTestingDailyScheduleController::class => SiteTestingDailyScheduleController::class,
        ],
        'factories' => [
            RoleController::class => RoleControllerFactory::class,
            SiteSearchControllerFactory::class => SiteSearchControllerFactory::class,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map'        => [
            'siteRiskAndScore'           =>
                __DIR__ . '/../view/site/vehicle-testing-station/partials/siteRiskAndScore.phtml',
            'brakeTestConfiguration'     =>
                __DIR__ . '/../view/site/vehicle-testing-station/partials/brakeTestConfiguration.phtml',
        ],
    ],
];
