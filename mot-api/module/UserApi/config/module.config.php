<?php

use UserApi\Application\Controller\AccountController;
use UserApi\Application\Controller\ApplicationController;
use UserApi\Message\Controller\MessageController;
use UserApi\HelpDesk\Controller\SearchPersonController;
use UserApi\SpecialNotice\Controller\SpecialNoticeBroadcastController;
use UserApi\SpecialNotice\Controller\SpecialNoticeContentController;
use UserApi\SpecialNotice\Controller\SpecialNoticeController;
use UserApi\SpecialNotice\Controller\SpecialNoticeOverdueController;
use UserApi\Factory\SpecialNoticeOverdueControllerFactory;

return [
    'controllers' => [
        'invokables' => [
            ApplicationController::class => ApplicationController::class,
            AccountController::class => AccountController::class,
            SpecialNoticeContentController::class => SpecialNoticeContentController::class,
            SpecialNoticeBroadcastController::class => SpecialNoticeBroadcastController::class,
            SpecialNoticeController::class => SpecialNoticeController::class,
            SearchPersonController::class => SearchPersonController::class,
            MessageController::class => MessageController::class,
        ],
        'factories' => [
            SpecialNoticeOverdueController::class => SpecialNoticeOverdueControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'applications-for-user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/user/:userId/application',
                    'defaults' => [
                        'controller' => ApplicationController::class,
                    ],
                    'constraints' => [
                        'userId' => '[0-9]+',
                    ],
                ],
            ],
            'search-person' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/search-person',
                    'defaults' => [
                        'controller' => SearchPersonController::class,
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'user-account' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/user-account[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => AccountController::class,
                    ],
                ],
            ],
            'special-notice-content' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/special-notice-content[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SpecialNoticeContentController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'publish' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/publish',
                            'defaults' => [
                                'controller' => SpecialNoticeContentController::class,
                                'action' => 'publish',
                            ],
                        ],
                    ],
                ],
            ],
            'special-notice-broadcast' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/special-notice-broadcast',
                    'defaults' => [
                        'controller' => SpecialNoticeBroadcastController::class,
                    ],
                ],
            ],
            'special-notice-overdue' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/special-notice-overdue',
                    'defaults' => [
                        'controller' => SpecialNoticeOverdueController::class,
                    ],
                ],
            ],
            'message' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/message',
                    'defaults' => [
                        'controller' => MessageController::class,
                    ],
                ],
            ],
        ],
    ],
];
