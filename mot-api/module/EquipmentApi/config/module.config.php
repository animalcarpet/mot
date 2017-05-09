<?php

use EquipmentApi\Controller\EquipmentModelController;
use EquipmentApi\Factory\Controller\EquipmentModelControllerFactory;
use EquipmentApi\Service\EquipmentModelService;
use EquipmentApi\Factory\Service\EquipmentModelServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            EquipmentModelService::class => EquipmentModelServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            EquipmentModelController::class => EquipmentModelControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'equipment' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/equipment-model',
                    'defaults' => [
                        'controller' => EquipmentModelController::class,
                    ],
                ],
            ],
        ],
    ],
];
