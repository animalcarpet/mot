<?php

use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use DvsaMotEnforcement\Factory\MotTestSearchControllerFactory as EnforcementMotTestSearchControllerFactory;
use Vehicle\Factory\Service\VehicleCatalogServiceFactory;
use Vehicle\Service\VehicleCatalogService;

return [
    'router'        => ['routes' => require __DIR__ . '/routes.config.php'],
    'controllers'   => [
        'invokables' => [
        ],
        'factories' => [
            EnforcementMotTestSearchController::class => EnforcementMotTestSearchControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            VehicleCatalogService::class => VehicleCatalogServiceFactory::class,
        ],
    ],
    'view_manager'  => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
