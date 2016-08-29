<?php

use DvsaEventApi\Controller\EventController;
use DvsaEventApi\Controller\EventPersonCreationController;
use DvsaEventApi\Factory\Controller\EventPersonCreationControllerFactory;

return [
    'controllers'     => [
        'factories'  => [
            EventPersonCreationController::class => EventPersonCreationControllerFactory::class,
        ],
        'invokables' => [
            EventController::class       => EventController::class,
        ],
    ],
    'router'          => [
        'routes' => [
            'event-list'     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/event/list/:type/:id',
                    'constraints' => [
                        'type' => 'ae|site|person',
                        'id' => '[1-9]+[0-9]*',
                    ],
                    'defaults'    => [
                        'controller' => EventController::class,
                    ],
                ],
            ],
            'event'     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/event/:id',
                    'constraints' => [
                        'id' => '[1-9]+[0-9]*',
                    ],
                    'defaults'    => [
                        'controller' => EventController::class,
                    ],
                ],
            ],
            'event-add-person'     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/event/add/person/:id',
                    'constraints' => [
                        'id' => '[1-9]+[0-9]*',
                    ],
                    'defaults'    => [
                        'controller' => EventPersonCreationController::class,
                    ],
                ],
            ],
        ],
    ],
];
