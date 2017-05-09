<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Api\RegistrationModule\Controller\RegistrationController;
use Dvsa\Mot\Api\RegistrationModule\Factory\Controller\RegistrationControllerFactory;

return [
    'factories' => [
        RegistrationController::class => RegistrationControllerFactory::class,
    ],
];
