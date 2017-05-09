<?php

use MailerApi\Controller\MailerController;

return [
    'controllers' => [
        'invokables' => [
            MailerController::class => MailerController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'mailer' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/mailer',
                    'defaults' => [
                        'controller' => MailerController::class,
                    ],
                ],
                'child_routes' => [
                    'username-reminder' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/username-reminder',
                            'defaults' => [
                                'controller' => MailerController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
