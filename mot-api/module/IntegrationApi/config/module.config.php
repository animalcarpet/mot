<?php

use IntegrationApi\Factory\Controller\DvlaVehicleUpdatedFactory;
use IntegrationApi\OpenInterface\Controller\OpenInterfaceMotTestController;
use IntegrationApi\TransportForLondon\Controller\TransportForLondonMotTestController;
use IntegrationApi\DvlaVehicle\Controller\DvlaVehicleCreatedController;
use IntegrationApi\DvlaVehicle\Controller\DvlaVehicleUpdatedController;
use IntegrationApi\DvlaInfo\Controller\DvlaInfoMotHistoryController;

return [
    'router' => [
        'routes' => [
            'open-interface-mot-test' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/open-interface/mot-test-pass',
                    'defaults' => [
                        'controller' => OpenInterfaceMotTestController::class,
                    ],
                ],
            ],
            'transport-for-london-mot-test' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/transport-for-london/mot-test',
                    'defaults' => [
                        'controller' => TransportForLondonMotTestController::class,
                    ],
                ],
            ],
            'dvla-motinfo-mot-test' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/dvla-motinfo/mot-test-history',
                    'defaults' => [
                        'controller' => DvlaInfoMotHistoryController::class,
                    ],
                ],
            ],

            'dvla-vehicle-created' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/dvla-vehicle-created',
                    'defaults' => [
                        'controller' => DvlaVehicleCreatedController::class,
                    ],
                ],
            ],
            'dvla-vehicle-updated' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/dvla-vehicle-updated',
                    'defaults' => [
                        'controller' => DvlaVehicleUpdatedController::class,
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'invokables' => [
            OpenInterfaceMotTestController::class => OpenInterfaceMotTestController::class,
            TransportForLondonMotTestController::class => TransportForLondonMotTestController::class,
            DvlaVehicleCreatedController::class => DvlaVehicleCreatedController::class,
            DvlaInfoMotHistoryController::class => DvlaInfoMotHistoryController::class,
        ],
        'factories' => [
            DvlaVehicleUpdatedController::class => DvlaVehicleUpdatedFactory::class,
        ],
    ],
];
