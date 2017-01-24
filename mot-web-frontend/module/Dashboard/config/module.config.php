<?php

use Dashboard\Controller\MyApplicationsController;
use Dashboard\Controller\NotificationController;
use Dvsa\Mot\Frontend\PersonModule\Controller\QualificationDetailsController;
use Dashboard\Controller\UserStatsController;
use Dashboard\Factory\Controller\UserHomeControllerFactory;
use Dashboard\Factory\Controller\SecurityQuestionControllerFactory;
use Dashboard\Controller\UserTradeRolesController;
use Dashboard\Factory\Controller\PasswordControllerFactory;
use Dashboard\Factory\Controller\UserTradeRolesControllerFactory;
use Dashboard\ViewHelper\NotificationLinkViewHelper;
use Dvsa\Mot\Frontend\PersonModule\Factory\Service\QualificationDetailsServiceFactory;

return [

    'controllers'  => [
        'invokables' => [
            MyApplicationsController::class => MyApplicationsController::class,
            UserStatsController::class      => UserStatsController::class,
        ],
        'factories'  => [
            UserHomeControllerFactory::class         => UserHomeControllerFactory::class,
            UserTradeRolesController::class          => UserTradeRolesControllerFactory::class,
            SecurityQuestionControllerFactory::class => SecurityQuestionControllerFactory::class,
            PasswordControllerFactory::class         => PasswordControllerFactory::class
        ]
    ],
    'router'       => [
        'routes' => [
            'user-home' => [
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => UserHomeControllerFactory::class,
                        'action'     => 'userHome',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'stats'           => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => 'stats',
                            'defaults' => [
                                'controller' => UserStatsController::class,
                                'action'     => 'show',
                            ],
                        ],
                    ],
                    'my-applications' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => 'my-applications',
                            'defaults' => [
                                'controller' => MyApplicationsController::class,
                                'action'     => 'myApplications',
                            ],
                        ],
                    ],
                    'notification' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'notification',
                            'may_terminate' => false,
                        ],
                        'child_routes' => [
                            'item' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:notificationId',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                        'action' => 'notification',
                                    ],
                                ],
                            ],
                            'list' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                        'action' => 'inbox',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'archive' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/archive',
                                            'defaults' => [
                                                'controller' => NotificationController::class,
                                                'action' => 'archive',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'archive'   => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/archive',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                        'action'     => 'archiveNotification',
                                    ],
                                ],
                            ],
                            'action' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/action',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                        'action' => 'confirmNomination',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_map'        => [
            'notification/list' => __DIR__ . '/../view/dashboard/partials/notification/list.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'notificationLink' => NotificationLinkViewHelper::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            QualificationDetailsController::class => QualificationDetailsServiceFactory::class,
        ],
    ],
];
