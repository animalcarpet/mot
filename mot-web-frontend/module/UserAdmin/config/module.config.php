<?php

use UserAdmin\Controller\ChangeQualificationStatusController;
use UserAdmin\Controller\DemoTestRequestsController;
use UserAdmin\Controller\RecordDemoTestController;
use UserAdmin\Controller\UserSearchController;
use UserAdmin\Factory\Controller\ChangeQualificationStatusControllerFactory;
use UserAdmin\Factory\Controller\DrivingLicenceControllerFactory;
use UserAdmin\Factory\Controller\EmailAddressControllerFactory;
use UserAdmin\Factory\Controller\PersonRoleControllerFactory;
use UserAdmin\Factory\Controller\RecordDemoTestControllerFactory;
use UserAdmin\Factory\Controller\ResetAccountClaimByPostControllerFactory;
use UserAdmin\Factory\Controller\UserProfileControllerFactory;
use UserAdmin\Factory\Controller\UserSearchControllerFactory;

return [
    'controllers' => [
        'invokables' => [
            UserSearchController::class => UserSearchController::class,
        ],
        'factories' => [
            ResetAccountClaimByPostControllerFactory::class => ResetAccountClaimByPostControllerFactory::class,
            UserProfileControllerFactory::class => UserProfileControllerFactory::class,
            UserSearchControllerFactory::class => UserSearchControllerFactory::class,
            PersonRoleControllerFactory::class => PersonRoleControllerFactory::class,
            EmailAddressControllerFactory::class => EmailAddressControllerFactory::class,
            RecordDemoTestController::class => RecordDemoTestControllerFactory::class,
            ChangeQualificationStatusController::class => ChangeQualificationStatusControllerFactory::class,
            DrivingLicenceControllerFactory::class => DrivingLicenceControllerFactory::class,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            'user-admin' => __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            'user_admin' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/user-admin',
                    'defaults' => [
                        'action' => 'index'
                    ],
                ],
                'child_routes' => [
                    'user-search' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/search',
                            'defaults' => [
                                'controller' => UserSearchControllerFactory::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'demo-test-requests' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/demo-test-requests',
                            'defaults' => [
                                'controller' => DemoTestRequestsController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'download-csv' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/csv',
                                    'defaults' => [
                                        'controller' => DemoTestRequestsController::class,
                                        'action' => 'downloadCsv'
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'user-search-results' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/results',
                            'defaults' => [
                                'controller' => UserSearchControllerFactory::class,
                                'action' => 'results',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
