<?php

use PersonApi\Factory\Helper\PersonDetailsChangeNotificationHelperFactory;
use PersonApi\Factory\Service\BasePersonServiceFactory;
use PersonApi\Factory\Service\DashboardServiceFactory;
use PersonApi\Factory\Service\DuplicateEmailCheckerServiceFactory;
use PersonApi\Factory\Service\LicenceDetailsServiceFactory;
use PersonApi\Factory\Service\PasswordExpiryNotificationServiceFactory;
use PersonApi\Factory\Service\PasswordExpiryServiceFactory;
use PersonApi\Factory\Service\PasswordServiceFactory;
use PersonApi\Factory\Service\PersonAddressServiceFactory;
use PersonApi\Factory\Service\PersonalAuthorisationForMotTestingServiceFactory;
use PersonApi\Factory\Service\PersonalDetailsServiceFactory;
use PersonApi\Factory\Service\PersonContactServiceFactory;
use PersonApi\Factory\Service\PersonDateOfBirthServiceFactory;
use PersonApi\Factory\Service\PersonEventServiceFactory;
use PersonApi\Factory\Service\PersonNameServiceFactory;
use PersonApi\Factory\Service\PersonRoleServiceFactory;
use PersonApi\Factory\Service\PersonServiceFactory;
use PersonApi\Factory\Service\PersonTradeRoleServiceFactory;
use PersonApi\Factory\Service\TelephoneServiceFactory;
use PersonApi\Factory\Service\UserStatsServiceFactory;
use PersonApi\Factory\Service\Validator\ChangePasswordValidatorFactory;
use PersonApi\Factory\Validator\BasePersonValidatorFactory;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;
use PersonApi\Service\BasePersonService;
use PersonApi\Service\DashboardService;
use PersonApi\Service\DuplicateEmailCheckerService;
use PersonApi\Service\LicenceDetailsService;
use PersonApi\Service\PasswordExpiryNotificationService;
use PersonApi\Service\PasswordExpiryService;
use PersonApi\Service\PasswordService;
use PersonApi\Service\PersonAddressService;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\PersonalDetailsService;
use PersonApi\Service\PersonContactService;
use PersonApi\Service\PersonDateOfBirthService;
use PersonApi\Service\PersonEventService;
use PersonApi\Service\PersonNameService;
use PersonApi\Service\PersonRoleService;
use PersonApi\Service\PersonService;
use PersonApi\Service\PersonTradeRoleService;
use PersonApi\Service\TelephoneService;
use PersonApi\Service\UserStatsService;
use PersonApi\Service\Validator\BasePersonValidator;
use PersonApi\Service\Validator\ChangePasswordValidator;
use PersonApi\Service\Validator\PersonalDetailsValidator;

return [
    'factories' => [
        BasePersonService::class => BasePersonServiceFactory::class,
        BasePersonValidator::class => BasePersonValidatorFactory::class,
        LicenceDetailsService::class => LicenceDetailsServiceFactory::class,
        PersonalAuthorisationForMotTestingService::class => PersonalAuthorisationForMotTestingServiceFactory::class,
        PersonalDetailsService::class => PersonalDetailsServiceFactory::class,
        PersonService::class => PersonServiceFactory::class,
        PersonRoleService::class => PersonRoleServiceFactory::class,
        UserStatsService::class => UserStatsServiceFactory::class,
        DashboardService::class => DashboardServiceFactory::class,
        PersonContactService::class => PersonContactServiceFactory::class,
        ChangePasswordValidator::class => ChangePasswordValidatorFactory::class,
        PasswordService::class => PasswordServiceFactory::class,
        PasswordExpiryService::class => PasswordExpiryServiceFactory::class,
        PasswordExpiryNotificationService::class => PasswordExpiryNotificationServiceFactory::class,
        PersonEventService::class => PersonEventServiceFactory::class,
        PersonAddressService::class => PersonAddressServiceFactory::class,
        PersonTradeRoleService::class => PersonTradeRoleServiceFactory::class,
        PersonNameService::class => PersonNameServiceFactory::class,
        PersonDateOfBirthService::class => PersonDateOfBirthServiceFactory::class,
        TelephoneService::class => TelephoneServiceFactory::class,
        PersonDetailsChangeNotificationHelper::class => PersonDetailsChangeNotificationHelperFactory::class,
        DuplicateEmailCheckerService::class => DuplicateEmailCheckerServiceFactory::class,
    ],
    'invokables' => [
        PersonGenerator::class => PersonGenerator::class,
        PersonContactGenerator::class => PersonContactGenerator::class,
        PersonalDetailsValidator::class => PersonalDetailsValidator::class,
    ],
];
