<?php

use Equipment\Controller\EquipmentController;

return [
    'router' => [
        'routes' => [
            'equipment' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/equipment',
                    'defaults' => [
                        'controller' => EquipmentController::class,
                        'action' => 'displayEquipmentList',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            EquipmentController::class => EquipmentController::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],
];
