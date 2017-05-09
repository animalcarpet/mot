<?php

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller\LogoutControllerFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller\SecurityControllerFactory;

return [

    'factories' => [
        SecurityController::class => SecurityControllerFactory::class,
        LogoutController::class => LogoutControllerFactory::class,
    ],
];
