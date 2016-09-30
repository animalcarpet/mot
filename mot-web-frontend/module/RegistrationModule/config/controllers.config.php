<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

use Dvsa\Mot\Frontend\RegistrationModule\Controller\AddressController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\CompletedController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\CreateAccountController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\DetailsController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\IndexController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\PasswordController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\SecurityQuestionOneController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\SecurityQuestionTwoController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\SummaryController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\AddressControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\CompletedControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\CreateAccountControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\DetailsControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\IndexControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\PasswordControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\SecurityQuestionOneControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\SecurityQuestionTwoControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\SummaryControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\EmailController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\EmailControllerFactory;

return [
    'factories' => [
        AddressController::class                => AddressControllerFactory::class,
        CompletedController::class              => CompletedControllerFactory::class,
        CreateAccountController::class          => CreateAccountControllerFactory::class,
        DetailsController::class                => DetailsControllerFactory::class,
        IndexController::class                  => IndexControllerFactory::class,
        PasswordController::class               => PasswordControllerFactory::class,
        SecurityQuestionOneController::class    => SecurityQuestionOneControllerFactory::class,
        SecurityQuestionTwoController::class    => SecurityQuestionTwoControllerFactory::class,
        SummaryController::class                => SummaryControllerFactory::class,
        EmailController::class                  => EmailControllerFactory::class,
    ],
];
