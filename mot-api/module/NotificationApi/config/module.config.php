<?php

use NotificationApi\Controller\NotificationController;
use NotificationApi\Controller\NotificationActionController;
use NotificationApi\Controller\PersonNotificationController;
use NotificationApi\Controller\PersonReadNotificationController;

return [
    'controllers' => [
        'invokables' => [
            NotificationController::class           => NotificationController::class,
            NotificationActionController::class     => NotificationActionController::class,
            PersonNotificationController::class     => PersonNotificationController::class,
            PersonReadNotificationController::class => PersonReadNotificationController::class,
        ],
    ],
    'router'      => [
        'routes' => [
            'notification' => [
                'type'         => 'segment',
                'options'      => [
                    'route' => '/notification',
                ],
                'child_routes' => [

                    'item'   => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/:id',
                            'defaults' => [
                                'controller' => NotificationController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [

                            'read' => [
                                'type'          => 'segment',
                                'options'       => [
                                    'route'    => '/read',
                                    'defaults' => [
                                        'controller' => NotificationController::class,
                                    ],
                                ],
                                'may_terminate' => true,
                            ],

                            'action' => [
                                'type'          => 'segment',
                                'options'       => [
                                    'route'    => '/action',
                                    'defaults' => [
                                        'controller' => NotificationActionController::class,
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],

                    'person' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/person/:personId',
                            'defaults' => [
                                'controller' => PersonNotificationController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [

                            'read' => [
                                'type'          => 'segment',
                                'options'       => [
                                    'route'    => '/read',
                                    'defaults' => [
                                        'controller' => PersonReadNotificationController::class,
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
