<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Frontend\PersonModule\Controller\QualificationDetailsController;
use Dvsa\Mot\Frontend\PersonModule\Factory\Service\QualificationDetailsServiceFactory;

return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'service_manager' => [
        'factories' => [
            QualificationDetailsController::class => QualificationDetailsServiceFactory::class,
        ],
    ],
];
