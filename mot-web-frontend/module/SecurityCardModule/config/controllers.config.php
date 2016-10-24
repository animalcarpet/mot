<?php

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\AlreadyHasRegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardHardStopController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Controller\AlreadyHasRegisteredCardControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Controller\RegisterCardHardStopControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Controller\RegisterCardInformationControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\OrderNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller\CardOrderCsvReportController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller\CardOrderReportListController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Controller\CardOrderCsvReportControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\RegisterCardInformationNewUserController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller\OrderNewCardControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Controller\RegisteredCardControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Factory\Controller\RegisterCardInformationNewUserControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Factory\Controller\NewUserOrderCardControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderReviewController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller\CardOrderReviewControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderAddressController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller\CardOrderAddressControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderConfirmationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller\CardOrderConfirmationControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Controller\CardOrderReportListControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Controller\LostOrForgottenCardControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\ForgotSecurityQuestionController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Controller\ForgotSecurityQuestionControllerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\AlreadyOrderedNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller\AlreadyOrderedNewCardControllerFactory;

return [
    'factories' => [
        OrderNewCardController::class => OrderNewCardControllerFactory::class,
        CardOrderAddressController::class => CardOrderAddressControllerFactory::class,
        CardOrderConfirmationController::class => CardOrderConfirmationControllerFactory::class,
        CardOrderReviewController::class => CardOrderReviewControllerFactory::class,
        RegisteredCardController::class => RegisteredCardControllerFactory::class,
        RegisterCardInformationController::class => RegisterCardInformationControllerFactory::class,
        RegisterCardInformationNewUserController::class => RegisterCardInformationNewUserControllerFactory::class,
        NewUserOrderCardController::class => NewUserOrderCardControllerFactory::class,
        LostOrForgottenCardController::class => LostOrForgottenCardControllerFactory::class,
        ForgotSecurityQuestionController::class => ForgotSecurityQuestionControllerFactory::class,
        CardOrderReportListController::class => CardOrderReportListControllerFactory::class,
        CardOrderCsvReportController::class => CardOrderCsvReportControllerFactory::class,
        RegisterCardHardStopController::class => RegisterCardHardStopControllerFactory::class,
        AlreadyOrderedNewCardController::class => AlreadyOrderedNewCardControllerFactory::class,
        AlreadyHasRegisteredCardController::class => AlreadyHasRegisteredCardControllerFactory::class,
    ]
];
