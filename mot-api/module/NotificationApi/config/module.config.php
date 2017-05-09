<?php

use NotificationApi\Controller\NotificationController;
use NotificationApi\Controller\NotificationActionController;
use NotificationApi\Controller\PersonNotificationController;
use NotificationApi\Controller\PersonReadNotificationController;

return [
    'controllers' => [
        'invokables' => [
            NotificationActionController::class => NotificationActionController::class,
            PersonReadNotificationController::class => PersonReadNotificationController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'notification' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/notification',
                    'defaults' => [
                        'controller' => NotificationController::class,
                    ],
                    'verb' => 'post',
                ],
                'may_terminate' => true,
                'child_routes' => [

                    'item' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:id',
                            'defaults' => [
                                'controller' => NotificationController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [

                            'read' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/read',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'action' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/action',
                                    'defaults' => [
                                        'controller' => NotificationActionController::class,
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'archive' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/archive',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                        'action' => 'archive',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],

                    'person' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/person/:personId',
                            'defaults' => [
                                'controller' => PersonNotificationController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [

                            'read' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/read',
                                    'defaults' => [
                                        'controller' => PersonReadNotificationController::class,
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'unread-count' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/unread-count',
                                    'defaults' => [
                                        'controller' => PersonNotificationController::class,
                                        'action' => 'unreadCount',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
