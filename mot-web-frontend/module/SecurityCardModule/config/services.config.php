<?php

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardHardStopAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardHardStopController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Action\RegisterCardHardStopActionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Listener\RegisterCardHardStopListenerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service\RegisterCardHardStopConditionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service\RegisterCardInformationCookieServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service\RegisterCardServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Listener\RegisterCardHardStopListener;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderCsvReportAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderReportListAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Action\CardOrderCsvReportActionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Action\CardOrderReportListActionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service\OrderNewSecurityCardSessionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Listener\CardPinValidationListenerFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Service\RegisteredCardServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Listener\CardPinValidationListener;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\Factory\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Factory\Service\SecurityCardServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggleFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service\OrderSecurityCardAddressServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Service\LostOrForgottenSessionServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Service\LostOrForgottenServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Factory\Security\SecurityCardGuardFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Factory\Service\TwoFactorNominationNotificationServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service\OrderSecurityCardStepServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderNewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action\CardOrderNewActionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderAddressAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action\CardOrderAddressActionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderReviewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action\CardOrderReviewActionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action\CardOrderProtectionFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardEventService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service\OrderSecurityCardEventServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardNotificationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service\OrderSecurityCardNotificationServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\AlreadyOrderedCardCookieService;

return [
    'factories' => [
        RegisteredCardService::class       => RegisteredCardServiceFactory::class,
        RegisterCardService::class         => RegisterCardServiceFactory::class,
        AlreadyLoggedInTodayWithLostForgottenCardCookieService::class => AlreadyLoggedInTodayWithLostForgottenCardCookieServiceFactory::class,
        RegisterCardInformationCookieService::class => RegisterCardInformationCookieServiceFactory::class,
        OrderSecurityCardStepService::class => OrderSecurityCardStepServiceFactory::class,
        OrderNewSecurityCardSessionService::class => OrderNewSecurityCardSessionFactory::class,
        TwoFaFeatureToggle::class => TwoFaFeatureToggleFactory::class,
        SecurityCardService::class => SecurityCardServiceFactory::class,
        SecurityCardGuard::class => SecurityCardGuardFactory::class,
        OrderSecurityCardAddressService::class => OrderSecurityCardAddressServiceFactory::class,
        LostOrForgottenSessionService::class => LostOrForgottenSessionServiceFactory::class,
        LostOrForgottenService::class => LostOrForgottenServiceFactory::class,
        CardOrderCsvReportAction::class => CardOrderCsvReportActionFactory::class,
        CardOrderReportListAction::class => CardOrderReportListActionFactory::class,
        RegisterCardHardStopController::class => RegisterCardHardStopController::class,
        RegisterCardHardStopListener::class => RegisterCardHardStopListenerFactory::class,
        CardPinValidationListener::class => CardPinValidationListenerFactory::class,
        RegisterCardHardStopCondition::class => RegisterCardHardStopConditionFactory::class,
        RegisterCardHardStopAction::class => RegisterCardHardStopActionFactory::class,
        TwoFactorNominationNotificationService::class => TwoFactorNominationNotificationServiceFactory::class,
        CardOrderNewAction::class => CardOrderNewActionFactory::class,
        CardOrderAddressAction::class => CardOrderAddressActionFactory::class,
        CardOrderReviewAction::class => CardOrderReviewActionFactory::class,
        CardOrderProtection::class => CardOrderProtectionFactory::class,
        OrderSecurityCardEventService::class => OrderSecurityCardEventServiceFactory::class,
        OrderSecurityCardNotificationService::class => OrderSecurityCardNotificationServiceFactory::class,
        AlreadyOrderedCardCookieService::class => AlreadyOrderedCardCookieService::class,
    ]
];
