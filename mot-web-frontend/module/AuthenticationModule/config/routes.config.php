<?php

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\RegisterCardController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\RegisterCardInformationController;

return [
    'router' => [
        'routes' => [
            'login' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => SecurityController::class,
                        'action'     => 'login',
                    ],
                ],
                'may_terminate' => true,
            ],
            'logout' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => LogoutController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
        ],
    ],
];
