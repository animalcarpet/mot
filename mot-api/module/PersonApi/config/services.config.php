<?php

use PersonApi\Factory\Service\BasePersonServiceFactory;
use PersonApi\Factory\Service\DashboardServiceFactory;
use PersonApi\Factory\Service\PasswordExpiryServiceFactory;
use PersonApi\Factory\Service\PersonalAuthorisationForMotTestingServiceFactory;
use PersonApi\Factory\Service\PersonalDetailsServiceFactory;
use PersonApi\Factory\Service\PersonContactServiceFactory;
use PersonApi\Factory\Service\PersonServiceFactory;
use PersonApi\Factory\Service\UserStatsServiceFactory;
use PersonApi\Factory\Validator\BasePersonValidatorFactory;
use PersonApi\Factory\Service\PersonRoleServiceFactory;
use PersonApi\Service\PasswordExpiryService;
use PersonApi\Service\PersonEventService;
use PersonApi\Factory\Service\PersonEventServiceFactory;
use PersonApi\Service\BasePersonService;
use PersonApi\Service\DashboardService;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\PersonalDetailsService;
use PersonApi\Service\PersonContactService;
use PersonApi\Service\PersonService;
use PersonApi\Service\PersonRoleService;
use PersonApi\Service\UserStatsService;
use PersonApi\Service\Validator\BasePersonValidator;
use PersonApi\Service\Validator\PersonalDetailsValidator;
use PersonApi\Service\Validator\ChangePasswordValidator;
use PersonApi\Factory\Service\Validator\ChangePasswordValidatorFactory;
use PersonApi\Factory\Service\PasswordServiceFactory;
use PersonApi\Service\PasswordService;
use PersonApi\Service\PasswordExpiryNotificationService;
use PersonApi\Factory\Service\PasswordExpiryNotificationServiceFactory;

return [
    'factories'  => [
        BasePersonService::class                         => BasePersonServiceFactory::class,
        BasePersonValidator::class                       => BasePersonValidatorFactory::class,
        PersonalAuthorisationForMotTestingService::class => PersonalAuthorisationForMotTestingServiceFactory::class,
        PersonalDetailsService::class                    => PersonalDetailsServiceFactory::class,
        PersonService::class                             => PersonServiceFactory::class,
        PersonRoleService::class                         => PersonRoleServiceFactory::class,
        UserStatsService::class                          => UserStatsServiceFactory::class,
        DashboardService::class                          => DashboardServiceFactory::class,
        PersonContactService::class                      => PersonContactServiceFactory::class,
        ChangePasswordValidator::class                   => ChangePasswordValidatorFactory::class,
        PasswordService::class                           => PasswordServiceFactory::class,
        PasswordExpiryService::class                     => PasswordExpiryServiceFactory::class,
        PasswordExpiryNotificationService::class         => PasswordExpiryNotificationServiceFactory::class,
        PersonEventService::class                        => PersonEventServiceFactory::class,
    ],
    'invokables' => [
        PersonGenerator::class          => PersonGenerator::class,
        PersonContactGenerator::class   => PersonContactGenerator::class,
        PersonalDetailsValidator::class => PersonalDetailsValidator::class,
    ],
];