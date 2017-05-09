<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Event\Controller\EventController;
use Event\Controller\EventCompletedController;
use Event\Controller\EventRecordController;
use Event\Controller\EventSummaryController;
use Event\Controller\EventOutcomeController;

return [
    'router' => [
        'routes' => [
            'event-list' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/event/list/:type/:id',
                    'constraints' => [
                        'type' => 'ae|site|person',
                        'id' => '[1-9]+[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => EventController::class,
                        'action' => 'list',
                    ],
                ],
            ],
            'event-detail' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/event/:type/:id/:event-id',
                    'constraints' => [
                        'type' => 'ae|site|person',
                        'id' => '[1-9]+[0-9]*',
                        'event-id' => '[1-9]+[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => EventController::class,
                        'action' => 'detail',
                    ],
                ],
            ],
            'event-add' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/event/:type/add',
                    'constraints' => [
                        'type' => 'AE|NT',
                    ],
                    'defaults' => [
                        'controller' => EventController::class,
                        'action' => 'create',
                    ],
                ],
            ],
            'event-manual-add' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/event/add/:type/:id',
                    'constraints' => [
                        'type' => 'ae|site|person',
                        'id' => '[1-9]+[0-9]*',
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'start' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/start[/]',
                            'defaults' => [
                                'controller' => EventRecordController::class,
                                'action' => 'start',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'record' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/record[/]',
                            'defaults' => [
                                'controller' => EventRecordController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'outcome' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/outcome[/]',
                            'defaults' => [
                                'controller' => EventOutcomeController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'summary' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/summary[/]',
                            'defaults' => [
                                'controller' => EventSummaryController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'completed' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/completed[/]',
                            'defaults' => [
                                'controller' => EventCompletedController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
