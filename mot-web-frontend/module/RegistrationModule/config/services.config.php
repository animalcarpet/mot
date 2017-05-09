<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Service\PasswordServiceFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Service\RegisterUserServiceFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Service\RegistrationSessionServiceFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Service\RegistrationStepServiceFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\PasswordService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;

return [
    'factories' => [
        PasswordService::class => PasswordServiceFactory::class,
        RegistrationSessionService::class => RegistrationSessionServiceFactory::class,
        RegisterUserService::class => RegisterUserServiceFactory::class,
        RegistrationStepService::class => RegistrationStepServiceFactory::class,
    ],
];
