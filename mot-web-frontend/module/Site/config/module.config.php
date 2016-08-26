<?php

use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use Site\Controller\MotTestLogController;
use Site\Controller\RoleController;
use Site\Controller\SiteController;
use Site\Controller\SiteTestingDailyScheduleController;
use Site\Factory\Controller\MotTestLogControllerFactory;
use Site\Factory\Controller\RoleControllerFactory;
use Site\Factory\Controller\SiteControllerFactory;
use Site\Factory\Controller\SiteSearchControllerFactory;
use Site\UpdateVtsProperty\UpdateVtsPropertyController;

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
                    'route'    => '/vehicle-testing-station/:vehicleTestingStationId/:nomineeId/confirm-nomination/:roleCode',
                    'defaults' => [
                        'controller' => RoleController::class,
                        'action'     => 'confirmNomination',
                    ],
                ],
            ],
            'vehicle-testing-station'            => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => SiteController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'vehicle-testing-station-rag-status'             => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/risk-assessment',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => SiteController::class,
                        'action'     => 'risk-assessment',
                    ],
                ],
            ],
            'vehicle-testing-station-add-rag-status'             => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/add-risk-assessment',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => SiteController::class,
                        'action'     => 'add-risk-assessment',
                    ],
                ],
            ],
            'vehicle-testing-station-cancel-add-rag-status'             => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/add-risk-assessment/cancel',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => SiteController::class,
                        'action'     => 'cancel-add-risk-assessment',
                    ],
                ],
            ],
            'vehicle-testing-station-add-rag-status-confirmation'             => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/add-risk-assessment/confirmation',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => SiteController::class,
                        'action'     => 'add-risk-assessment-confirmation',
                    ],
                ],
            ],
            'vehicle-testing-station-test-quality' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/vehicle-testing-station/:id/test-quality[/:month][/:year]',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'month' => '[0-9]+',
                        'year' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SiteController::class,
                        'action' => 'testQuality',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'user-test-quality' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/user/:userId/group/:group',
                            'constraints' => [
                                'id' => '[0-9]+',
                                'group' => 'A|B',
                            ],
                            'defaults' => [
                                'controller' => SiteController::class,
                                'action' => 'userTestQuality',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'csv' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/csv',
                                    'defaults' => [
                                        'controller' => SiteController::class,
                                        'action' => 'userTestQualityCsv',
                                    ],
                                ],
                            ]
                        ],
                    ],
                    'csv' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/csv/group/:group',
                            'constraints' => [
                                'group' => 'A|B',
                            ],
                            'defaults' => [
                                'controller' => SiteController::class,
                                'action' => 'testQualityCsv',
                            ],
                        ],
                    ]
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
                    'route'       => '/vehicle-testing-station/site/:id',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => SiteController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'vehicle-testing-station-edit-property'     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/:propertyName/change',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'propertyName' => 'name|classes|status|type|email|phone|address|country',
                    ],
                    'defaults'    => [
                        'controller' => UpdateVtsPropertyController::class,
                        'action'     => 'edit',
                    ],
                ],
            ],
            'vehicle-testing-station-edit-property-review'     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/vehicle-testing-station/:id/:propertyName/review/:formUuid',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'propertyName' => 'name|classes|status|type|email|phone|address|country',
                    ],
                    'defaults'    => [
                        'controller' => UpdateVtsPropertyController::class,
                        'action'     => 'review',
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
                                'controller' => SiteController::class,
                                'action'     => 'create',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'confirmation' => [
                                'type'    => 'literal',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'controller' => SiteController::class,
                                        'action'     => 'confirmation',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'configure-brake-test-defaults' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:id/configure-brake-test-defaults',
                            'defaults' => [
                                'id'         => '[0-9]+',
                                'controller' => SiteController::class,
                                'action'     => 'configureBrakeTestDefaults',
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
                                'action'     => 'remove',
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
                                'action'     => 'edit',
                            ],
                        ],
                    ],
                    'edit-testing-facilities'            => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:id/testing-facilities',
                            'defaults' => [
                                'siteId'     => '[0-9]+',
                                'controller' => SiteController::class,
                                'action'     => 'testingFacilities',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'confirmation' => [
                                'type'    => 'literal',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'controller' => SiteController::class,
                                        'action'     => 'testingFacilitiesConfirmation',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'mot-test-log'       => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/:id/mot-test-log',
                            'defaults'    => [
                                'id'         => '[0-9]+',
                                'controller' => MotTestLogController::class,
                                'action'     => 'index',

                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'download'    => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/csv',
                                    'defaults'    => [
                                        'controller' => MotTestLogController::class,
                                        'action'     => 'downloadCsv',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers'  => [
        'invokables' => [
            SiteTestingDailyScheduleController::class => SiteTestingDailyScheduleController::class,
        ],
        'factories'  => [
            MotTestLogController::class            => MotTestLogControllerFactory::class,
            RoleController::class                  => RoleControllerFactory::class,
            SiteSearchControllerFactory::class     => SiteSearchControllerFactory::class,
            SiteController::class                  => SiteControllerFactory::class,
        ],
        'abstract_factories' => [
            AutoWireFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map'        => [
            'siteRiskAndScore'           => __DIR__ . '/../view/site/vehicle-testing-station/partials/siteRiskAndScore.phtml',
            'brakeTestConfiguration'     => __DIR__ . '/../view/site/vehicle-testing-station/partials/brakeTestConfiguration.phtml',
        ],
    ],
];
