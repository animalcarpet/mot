<?php

use SiteApi\Controller\DefaultBrakeTestsController;
use SiteApi\Controller\EnforcementSiteAssessmentController;
use SiteApi\Controller\EnforcementSiteAssessmentValidateController;
use SiteApi\Controller\EquipmentController;
use SiteApi\Controller\MotTestInProgressController;
use SiteApi\Controller\MotTestLogController;
use SiteApi\Controller\SiteContactController;
use SiteApi\Controller\SiteController;
use SiteApi\Controller\SiteDetailsController;
use SiteApi\Controller\SiteEventController;
use SiteApi\Controller\SiteNameController;
use SiteApi\Controller\SiteOrganisationNameController;
use SiteApi\Controller\SitePositionController;
use SiteApi\Controller\SitePositionValidateController;
use SiteApi\Controller\SiteRoleController;
use SiteApi\Controller\SiteSearchController;
use SiteApi\Controller\SiteTestingDailyScheduleController;
use SiteApi\Controller\SiteTestingFacilitiesController;
use SiteApi\Controller\VehicleTestingStationAuthorisedClassesController;
use SiteApi\Factory\Controller\EnforcementSiteAssessmentControllerFactory;
use SiteApi\Factory\Controller\EnforcementSiteAssessmentValidateControllerFactory;
use SiteApi\Factory\Controller\MotTestInProgressControllerFactory;
use SiteApi\Factory\Controller\MotTestLogControllerFactory;
use SiteApi\Factory\Controller\SiteControllerFactory;
use SiteApi\Factory\Controller\SiteDetailsControllerFactory;
use SiteApi\Factory\Controller\SiteEventControllerFactory;
use SiteApi\Factory\Controller\SiteNameControllerFactory;
use SiteApi\Factory\Controller\SiteOrganisationNameControllerFactory;
use SiteApi\Factory\Controller\SiteSearchControllerFactory;
use SiteApi\Factory\Controller\SiteTestingFacilitiesControllerFactory;

return [
    'controllers' => [
        'invokables' => [
            VehicleTestingStationAuthorisedClassesController::class => VehicleTestingStationAuthorisedClassesController::class,
            SiteRoleController::class => SiteRoleController::class,
            SitePositionController::class => SitePositionController::class,
            EquipmentController::class => EquipmentController::class,
            SiteTestingDailyScheduleController::class => SiteTestingDailyScheduleController::class,
            DefaultBrakeTestsController::class => DefaultBrakeTestsController::class,
            SiteContactController::class => SiteContactController::class,
            SitePositionValidateController::class => SitePositionValidateController::class,
        ],
        'factories' => [
            EnforcementSiteAssessmentController::class => EnforcementSiteAssessmentControllerFactory::class,
            EnforcementSiteAssessmentValidateController::class => EnforcementSiteAssessmentValidateControllerFactory::class,
            MotTestLogController::class => MotTestLogControllerFactory::class,
            SiteSearchController::class => SiteSearchControllerFactory::class,
            SiteController::class => SiteControllerFactory::class,
            SiteNameController::class => SiteNameControllerFactory::class,
            SiteOrganisationNameController::class => SiteOrganisationNameControllerFactory::class,
            SiteTestingFacilitiesController::class => SiteTestingFacilitiesControllerFactory::class,
            SiteDetailsController::class => SiteDetailsControllerFactory::class,
            MotTestInProgressController::class => MotTestInProgressControllerFactory::class,
            SiteEventController::class => SiteEventControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site-manual-event' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/site/:siteId/event',
                    'defaults' => [
                        'controller' => SiteEventController::class,
                    ],
                ],
                'may_terminate' => true,
            ],
            'site-role' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/site/:siteId/person/:personId/role',
                    'constraints' => [
                        'siteId' => '[0-9]+',
                        'personId' => '[a-zA-Z]?[a-zA-Z0-9\.-_@]+',
                    ],
                    'defaults' => [
                        'controller' => SiteRoleController::class,
                    ],
                ],
            ],
            'site-position' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/site/:siteId/position[/:positionId]',
                    'constraints' => [
                        'siteId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SitePositionController::class,
                    ],
                ],
            ],
            'site-position-validate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/site/:siteId/position-validate',
                    'constraints' => [
                        'siteId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SitePositionValidateController::class,
                    ],
                ],
            ],
            'vehicle-testing-station' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/vehicle-testing-station[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SiteController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'vts-organisation-name' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/organisation/name',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => SiteOrganisationNameController::class,
                            ],
                        ],
                    ],
                    'vts-name' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/name',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => SiteNameController::class,
                            ],
                        ],
                    ],
                    'site-authorised-classes' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/authorised-classes',
                            'defaults' => [
                                'controller' => VehicleTestingStationAuthorisedClassesController::class,
                            ],
                        ],
                    ],
                    'equipment' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/equipment',
                            'defaults' => [
                                'controller' => EquipmentController::class,
                            ],
                        ],
                    ],
                    'default-brake-tests' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/default-brake-tests',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => DefaultBrakeTestsController::class,
                            ],
                        ],
                    ],
                    'opening-hours' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/opening-hours',
                            'defaults' => [
                                'controller' => SiteTestingDailyScheduleController::class,
                            ],
                        ],
                    ],
                    'testing-facilities' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/testing-facilities',
                            'defaults' => [
                                'controller' => SiteTestingFacilitiesController::class,
                            ],
                        ],
                    ],
                    'site-details' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/site-details',
                            'defaults' => [
                                'controller' => SiteDetailsController::class,
                            ],
                        ],
                    ],
                    'test-in-progress' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/test-in-progress',
                            'defaults' => [
                                'controller' => MotTestInProgressController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'update' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/count',
                                    'defaults' => [
                                        'controller' => MotTestInProgressController::class,
                                        'action' => 'count',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'risk-assessment' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/risk-assessment',
                            'defaults' => [
                                'controller' => EnforcementSiteAssessmentController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'risk-assessment-validate' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/validate',
                                    'defaults' => [
                                        'controller' => EnforcementSiteAssessmentValidateController::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'contact' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/contact[/:contactId]',
                            'constraints' => [
                                'contactId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => SiteContactController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'update' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/update',
                                    'defaults' => [
                                        'controller' => SiteContactController::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'contact-only' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/contact',
                            'defaults' => [
                                'controller' => SiteContactController::class,
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'mot-test-log' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/mot-test-log',
                            'defaults' => [
                                'controller' => MotTestLogController::class,
                                'action' => 'logData',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'summary' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/summary',
                                    'defaults' => [
                                        'controller' => MotTestLogController::class,
                                        'action' => 'summary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'vehicle-testing-station-search' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/vehicle-testing-station/search',
                    'defaults' => [
                        'controller' => SiteSearchController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'search-by-vts-number' => [
                      'type' => 'Segment',
                        'options' => [
                            'route' => '/number[/:id]',
                            'defaults' => [
                                'controller' => SiteSearchController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
