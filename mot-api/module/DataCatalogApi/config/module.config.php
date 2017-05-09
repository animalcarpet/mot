<?php

use \DataCatalogApi\Controller;

return [
    'controllers' => [
        'invokables' => [
            'DataCatalogApi\Controller\DataCatalog' => Controller\DataCatalogController::class,
            'DataCatalogApi\Controller\Make' => Controller\MakeController::class,
            'DataCatalogApi\Controller\Model' => Controller\ModelController::class,
            'DataCatalogApi\Controller\ModelDetail' => Controller\ModelDetailController::class,
            'DataCatalogApi\Controller\VehicleDictionary' => Controller\VehicleDictionaryController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'catalog' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/catalog',
                    'defaults' => [
                        'controller' => 'DataCatalogApi\Controller\DataCatalog',
                    ],
                ],
            ],
            'vehicle-dictionary' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/vehicle-dictionary',
                    'defaults' => [
                        'controller' => 'DataCatalogApi\Controller\VehicleDictionary',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'make' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/make[/:id]',
                            'defaults' => [
                                'controller' => 'DataCatalogApi\Controller\Make',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'sub' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/model[/:model]',
                                    'defaults' => [
                                        'controller' => 'DataCatalogApi\Controller\Model',
                                        'action' => 'getModels',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'sub' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/model-details',
                                            'defaults' => [
                                                'controller' => 'DataCatalogApi\Controller\ModelDetail',
                                                'action' => 'getModelDetails',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'modelByMakeId' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/models',
                                    'defaults' => [
                                        'controller' => 'DataCatalogApi\Controller\Model',
                                        'action' => 'getModelsByMakeId',
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
