<?php

use Organisation\Controller\MotTestLogController;
use Organisation\Controller\RoleController;
use Organisation\Factory\Controller\MotTestLogControllerFactory;
use Organisation\Factory\Controller\RoleControllerFactory;

return [
    'factories' => [
        RoleController::class => RoleControllerFactory::class,
        MotTestLogController::class => MotTestLogControllerFactory::class,
    ],
];
