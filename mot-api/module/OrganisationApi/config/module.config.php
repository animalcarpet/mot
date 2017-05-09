<?php

use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use OrganisationApi\Controller\AuthorisedExaminerController;
use OrganisationApi\Controller\AuthorisedExaminerPrincipalController;
use OrganisationApi\Controller\AuthorisedExaminerNameController;
use OrganisationApi\Controller\OrganisationPositionController;
use OrganisationApi\Controller\OrganisationRoleController;
use OrganisationApi\Controller\SiteController;
use OrganisationApi\Controller\SiteLinkController;
use OrganisationApi\Controller\OrganisationEventController;
use OrganisationApi\Factory\Controller\OrganisationEventControllerFactory;
use OrganisationApi\Factory\Controller\MotTestLogControllerFactory;
use OrganisationApi\Factory\Controller\AuthorisedExaminerNameControllerFactory;
use OrganisationApi\Factory\Controller\SiteControllerFactory;
use OrganisationApi\Factory\Controller\SiteLinkControllerFactory;
use OrganisationApi\Factory\Controller\AuthorisedExaminerStatusControllerFactory;

return [
    'controllers' => [
        'invokables' => [
            AuthorisedExaminerPrincipalController::class => AuthorisedExaminerPrincipalController::class,
            OrganisationPositionController::class => OrganisationPositionController::class,
            OrganisationRoleController::class => OrganisationRoleController::class,
        ],
        'factories' => [
            SiteController::class => SiteControllerFactory::class,
            SiteLinkController::class => SiteLinkControllerFactory::class,
            MotTestLogControllerFactory::class => MotTestLogControllerFactory::class,
            AuthorisedExaminerStatusControllerFactory::class => AuthorisedExaminerStatusControllerFactory::class,
            OrganisationEventController::class => OrganisationEventControllerFactory::class,
            AuthorisedExaminerNameController::class => AuthorisedExaminerNameControllerFactory::class,
        ],
        'abstract_factories' => [
            AutoWireFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'slot-purchase' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/slot-purchase',
                ],
                'child_routes' => [
                    'dd-slot-increment' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/dd-slot-increment',
                            'defaults' => [
                                'controller' => 'OrganisationApi\Controller\DDSlotIncrement',
                            ],
                        ],
                    ],
                ],
            ],
            'organisation' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/organisation/:organisationId',
                    'constraints' => [
                        'organisationId' => '[0-9]+',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'manual-event' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/event',
                            'defaults' => [
                                'controller' => OrganisationEventController::class,
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'organisation-name' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/name',
                            'defaults' => [
                                'controller' => AuthorisedExaminerNameController::class,
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'authorised-examiner' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/authorised-examiner[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => AuthorisedExaminerController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'area-offices' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/area-offices/list',
                            'defaults' => [
                                'controller' => AuthorisedExaminerStatusControllerFactory::class,
                                'action' => 'getAreaOffices',
                            ],
                        ],
                    ],
                    'status' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/status',
                            'defaults' => [
                                'controller' => AuthorisedExaminerStatusControllerFactory::class,
                            ],
                        ],
                    ],
                    'number' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/number[/:number]',
                            'defaults' => [
                                'controller' => AuthorisedExaminerController::class,
                                'action' => 'getAuthorisedExaminerByNumber',
                            ],
                        ],
                    ],
                    'mot-test-log' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/mot-test-log',
                            'defaults' => [
                                'controller' => MotTestLogControllerFactory::class,
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
                                        'controller' => MotTestLogControllerFactory::class,
                                        'action' => 'summary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'site' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/site',
                            'defaults' => [
                                'controller' => SiteController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'link' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/link[/:linkId]',
                                    'constraints' => [
                                        'linkId' => '\d+',
                                    ],
                                    'defaults' => [
                                        'controller' => SiteLinkController::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'authorised-examiner-principal' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/authorised-examiner/:authorisedExaminerId/authorised-examiner-principal[/:id]',
                    'constraints' => [
                        'authorisedExaminerId' => '[0-9]+',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => AuthorisedExaminerPrincipalController::class,
                    ],
                ],
            ],
            'organisation-position' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/organisation/:organisationId/position[/:positionId]',
                    'constraints' => [
                        'organisationId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => OrganisationPositionController::class,
                    ],
                ],
            ],
            'organisation-role' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/organisation/:organisationId/person/:personId/role',
                    'constraints' => [
                        'organisationId' => '[0-9]+',
                        'personId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => OrganisationRoleController::class,
                    ],
                ],
            ],
        ],
    ],
];
