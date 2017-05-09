<?php

use PersonApi\Controller\AuthorisedExaminerController;
use PersonApi\Controller\DashboardController;
use PersonApi\Controller\EditTelephoneController;
use PersonApi\Controller\MotTestingAuthorisationController;
use PersonApi\Controller\PasswordController;
use PersonApi\Controller\PasswordExpiryController;
use PersonApi\Controller\PersonalDetailsController;
use PersonApi\Controller\PersonAuthorisationController;
use PersonApi\Controller\PersonByLoginController;
use PersonApi\Controller\PersonContactController;
use PersonApi\Controller\PersonController;
use PersonApi\Controller\PersonCurrentMotTestController;
use PersonApi\Controller\PersonEmailController;
use PersonApi\Controller\PersonEventController;
use PersonApi\Controller\PersonPendingRoleController;
use PersonApi\Controller\PersonProfileRestrictedController;
use PersonApi\Controller\PersonProfileUnrestrictedController;
use PersonApi\Controller\PersonRoleController;
use PersonApi\Controller\PersonSiteCountController;
use PersonApi\Controller\PersonTradeRoleController;
use PersonApi\Controller\ResetClaimAccountController;
use PersonApi\Controller\ResetPinController;
use PersonApi\Controller\UpdateAddressController;
use PersonApi\Controller\UpdateLicenceDetailsController;
use PersonApi\Controller\UpdatePersonNameController;
use PersonApi\Controller\UpdatePersonDateOfBirthController;
use PersonApi\Controller\UserStatsController;
use PersonApi\Factory\Controller\AuthorisedExaminerControllerFactory;
use PersonApi\Factory\Controller\DashboardControllerFactory;
use PersonApi\Factory\Controller\EditTelephoneControllerFactory;
use PersonApi\Factory\Controller\MotTestingAuthorisationControllerFactory;
use PersonApi\Factory\Controller\PasswordControllerFactory;
use PersonApi\Factory\Controller\PasswordExpiryControllerFactory;
use PersonApi\Factory\Controller\PersonalDetailsControllerFactory;
use PersonApi\Factory\Controller\PersonAuthorisationControllerFactory;
use PersonApi\Factory\Controller\PersonByLoginControllerFactory;
use PersonApi\Factory\Controller\PersonContactControllerFactory;
use PersonApi\Factory\Controller\PersonControllerFactory;
use PersonApi\Factory\Controller\PersonCurrentMotTestControllerFactory;
use PersonApi\Factory\Controller\PersonEmailControllerFactory;
use PersonApi\Factory\Controller\PersonEventControllerFactory;
use PersonApi\Factory\Controller\PersonPendingRoleControllerFactory;
use PersonApi\Factory\Controller\PersonProfileRestrictedControllerFactory;
use PersonApi\Factory\Controller\PersonProfileUnrestrictedControllerFactory;
use PersonApi\Factory\Controller\PersonRoleControllerFactory;
use PersonApi\Factory\Controller\PersonSiteCountControllerFactory;
use PersonApi\Factory\Controller\PersonTradeRoleControllerFactory;
use PersonApi\Factory\Controller\ResetClaimAccountControllerFactory;
use PersonApi\Factory\Controller\ResetPinControllerFactory;
use PersonApi\Factory\Controller\UpdateAddressControllerFactory;
use PersonApi\Factory\Controller\UpdateLicenceDetailsControllerFactory;
use PersonApi\Factory\Controller\UpdatePersonNameControllerFactory;
use PersonApi\Factory\Controller\UpdatePersonDateOfBirthControllerFactory;
use PersonApi\Factory\Controller\UserStatsControllerFactory;

return [
    'factories' => [
        AuthorisedExaminerController::class => AuthorisedExaminerControllerFactory::class,
        EditTelephoneController::class => EditTelephoneControllerFactory::class,
        DashboardController::class => DashboardControllerFactory::class,
        UpdateLicenceDetailsController::class => UpdateLicenceDetailsControllerFactory::class,
        MotTestingAuthorisationController::class => MotTestingAuthorisationControllerFactory::class,
        PersonalDetailsController::class => PersonalDetailsControllerFactory::class,
        PersonAuthorisationController::class => PersonAuthorisationControllerFactory::class,
        PersonByLoginController::class => PersonByLoginControllerFactory::class,
        PersonController::class => PersonControllerFactory::class,
        PersonContactController::class => PersonContactControllerFactory::class,
        PersonCurrentMotTestController::class => PersonCurrentMotTestControllerFactory::class,
        PersonEventController::class => PersonEventControllerFactory::class,
        PersonPendingRoleController::class => PersonPendingRoleControllerFactory::class,
        PersonProfileRestrictedController::class => PersonProfileRestrictedControllerFactory::class,
        PersonProfileUnrestrictedController::class => PersonProfileUnrestrictedControllerFactory::class,
        PersonSiteCountController::class => PersonSiteCountControllerFactory::class,
        PersonRoleController::class => PersonRoleControllerFactory::class,
        ResetClaimAccountController::class => ResetClaimAccountControllerFactory::class,
        ResetPinController::class => ResetPinControllerFactory::class,
        UserStatsController::class => UserStatsControllerFactory::class,
        PasswordController::class => PasswordControllerFactory::class,
        PasswordExpiryController::class => PasswordExpiryControllerFactory::class,
        PersonTradeRoleController::class => PersonTradeRoleControllerFactory::class,
        UpdateAddressController::class => UpdateAddressControllerFactory::class,
        UpdatePersonNameController::class => UpdatePersonNameControllerFactory::class,
        UpdatePersonDateOfBirthController::class => UpdatePersonDateOfBirthControllerFactory::class,
        PersonEmailController::class => PersonEmailControllerFactory::class,
    ],
];
