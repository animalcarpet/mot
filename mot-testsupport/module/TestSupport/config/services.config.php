<?php

use Doctrine\ORM\EntityManager;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\HttpRestJson\ZendClient;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use TestSupport\Factory\CatUserServiceFactory;
use TestSupport\Factory\ClaimAccountServiceFactory;
use TestSupport\Factory\TesterAuthorisationStatusServiceFactory;
use TestSupport\Helper\NotificationsHelper;
use TestSupport\Helper\SitePermissionsHelper;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Service\AccountDataService;
use TestSupport\Service\AccountService;
use TestSupport\Service\AedmService;
use TestSupport\Service\AEService;
use TestSupport\Service\AreaOffice1Service;
use TestSupport\Service\AreaOffice2Service;
use TestSupport\Service\CatUserService;
use TestSupport\Service\CertificateReplacementService;
use TestSupport\Service\ClaimAccountService;
use TestSupport\Service\CronUserService;
use TestSupport\Service\CSCOService;
use TestSupport\Service\CSMService;
use TestSupport\Service\DocumentService;
use TestSupport\Service\DVLAManagerService;
use TestSupport\Service\DVLAOperativeService;
use TestSupport\Service\DvlaVehicleService;
use TestSupport\Service\FeaturesService;
use TestSupport\Service\FinanceUserService;
use TestSupport\Service\GVTSTesterService;
use TestSupport\Service\InactiveTesterService;
use TestSupport\Service\JsonErrorHandlingListener;
use TestSupport\Service\MotService;
use TestSupport\Service\PasswordResetService;
use TestSupport\Service\PaymentNotificationsAuditService;
use TestSupport\Service\SchemeManagerService;
use TestSupport\Service\SchemeUserService;
use TestSupport\Service\SiteRoleNominationService;
use TestSupport\Service\SiteUserDataService;
use TestSupport\Service\SlotTransactionService;
use TestSupport\Service\StatisticsAmazonCacheService;
use TestSupport\Service\TesterAuthorisationStatusService;
use TestSupport\Service\TesterService;
use TestSupport\Service\UserService;
use TestSupport\Service\VehicleExaminerService;
use TestSupport\Service\VehicleService;
use TestSupport\Service\VM10519UserService;
use TestSupport\Service\VM10619RoleManagementUpgradeService;
use TestSupport\Service\TwoFactorAuthCardService;
use TestSupport\Service\VtsService;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\GdsSurveyService;
use TestSupport\Factory\GdsSurveyServiceFactory;
use TestSupport\Service\SecurityQuestionsService;
use TestSupport\Factory\SecurityQuestionsServiceFactory;
use TestSupport\Factory\SiteRoleNominationServiceFactory;
use TestSupport\Service\OrganisationRoleNominationService;
use TestSupport\Factory\RoleNominationServiceFactory;
use TestSupport\Service\OneHundredMotTestsService;
use TestSupport\Factory\TestSupportOneHundredMotTestsServiceFactory;
use TestSupport\Service\VehicleTestingAdviceService;

return [
    'factories' => [
        TestSupportAccessTokenManager::class => function (ServiceLocatorInterface $sm) {
            $config = $sm->get('config');

            return new TestSupportAccessTokenManager($config['apiUrl']);
        },
        JsonErrorHandlingListener::class => function (ServiceLocatorInterface $sm) {
            return new JsonErrorHandlingListener();
        },
        \DvsaCommon\HttpRestJson\Client::class => function (ServiceLocatorInterface $sm) {
            $config = $sm->get('config');

            return new ZendClient(new \Zend\Http\Client(), $config['apiUrl']);
        },
        SiteUserDataService::class => function (ServiceLocatorInterface $sm) {
            return new SiteUserDataService(
                    $sm->get(NotificationsHelper::class),
                    $sm->get(SitePermissionsHelper::class),
                    $sm->get(AccountService::class)
                );
        },
        AccountDataService::class => \TestSupport\Factory\AccountDataServiceFactory::class,
        AccountService::class => function (ServiceLocatorInterface $sm) {
            $tokenManager = $sm->get(TestSupportAccessTokenManager::class);

            return new AccountService(
                    $sm->get(EntityManager::class),
                    $sm->get(Client::class),
                    $tokenManager,
                    $sm->get(SecurityQuestionsService::class)
                );
        },
        VehicleService::class => function (ServiceLocatorInterface $sm) {
            $entityManager = $sm->get(EntityManager::class);
            $tokenManager = $sm->get(TestSupportAccessTokenManager::class);
            $config = $sm->get('config');
            $vehicleServiceUrl = isset($config['api']) && isset($config['api']['vehicle_service_url']) ?
                    $config['api']['vehicle_service_url'] :
                    null;

            return new VehicleService(
                    $entityManager,
                    $tokenManager,
                    $vehicleServiceUrl
                );
        },
        DvlaVehicleService::class => function (ServiceLocatorInterface $sm) {
            return new DvlaVehicleService($sm->get(EntityManager::class));
        },
        VehicleTestingAdviceService::class => function (ServiceLocatorInterface $sm) {
            return new VehicleTestingAdviceService($sm->get(EntityManager::class));
        },
        PaymentNotificationsAuditService::class => function (ServiceLocatorInterface $sm) {
            return new PaymentNotificationsAuditService($sm->get(EntityManager::class));
        },
        SlotTransactionService::class => function (ServiceLocatorInterface $sm) {
            $tokenManager = $sm->get(TestSupportAccessTokenManager::class);

            return new SlotTransactionService($sm->get(Client::class), $tokenManager);
        },
        MotService::class => function (ServiceLocatorInterface $sm) {
            return new MotService($sm->get(EntityManager::class));
        },
        CertificateReplacementService::class => function (ServiceLocatorInterface $sm) {
            return new CertificateReplacementService($sm->get(EntityManager::class));
        },

        // @TODO after mot-common-web-module is part of composer remove the below lines as the module will
        // already have these services registered.
        ParamEncrypter::class => \DvsaCommon\Obfuscate\Factory\ParamEncrypterFactory::class,
        ParamObfuscator::class => \DvsaCommon\Obfuscate\Factory\ParamObfuscatorFactory::class,
        'ApplicationLog' => 'TestSupport\Factory\ApplicationLogFactory',
        CSCOService::class => \TestSupport\Factory\CSCOServiceFactory::class,
        CSMService::class => \TestSupport\Factory\CSMServiceFactory::class,
        DVLAManagerService::class => \TestSupport\Factory\DVLAManagerServiceFactory::class,
        DVLAOperativeService::class => \TestSupport\Factory\DVLAOperativeServiceFactory::class,
        AreaOffice1Service::class => \TestSupport\Factory\AreaOffice1ServiceFactory::class,
        AreaOffice2Service::class => \TestSupport\Factory\AreaOffice2ServiceFactory::class,
        CronUserService::class => \TestSupport\Factory\CronUserServiceFactory::class,
        FinanceUserService::class => \TestSupport\Factory\FinanceUserServiceFactory::class,
        UserService::class => \TestSupport\Factory\UserServiceFactory::class,
        VtsService::class => \TestSupport\Factory\VtsServiceFactory::class,
        TestSupportRestClientHelper::class => \TestSupport\Factory\TestSupportRestClientFactory::class,
        NotificationsHelper::class => \TestSupport\Factory\NotificationsHelperFactory::class,
        SitePermissionsHelper::class => \TestSupport\Factory\SitePermissionsHelperFactory::class,
        AEService::class => \TestSupport\Factory\AEServiceFactory::class,
        TesterService::class => \TestSupport\Factory\TesterServiceFactory::class,
        FeaturesService::class => \TestSupport\Factory\FeaturesServiceFactory::class,
        InactiveTesterService::class => \TestSupport\Factory\InactiveTesterServiceFactory::class,
        SchemeManagerService::class => \TestSupport\Factory\SchemeManagerServiceFactory::class,
        SchemeUserService::class => \TestSupport\Factory\SchemeUserServiceFactory::class,
        InactiveTesterService::class => \TestSupport\Factory\InactiveTesterServiceFactory::class,
        PasswordResetService::class => \TestSupport\Factory\PasswordResetServiceFactory::class,
        VehicleExaminerService::class => \TestSupport\Factory\VehicleExaminerServiceFactory::class,
        VM10519UserService::class => \TestSupport\Factory\VM10519UserServiceFactory::class,
        VM10619RoleManagementUpgradeService::class => \TestSupport\Factory\VM10619RoleManagementUpgradeServiceFactory::class,
        TesterAuthorisationStatusService::class => TesterAuthorisationStatusServiceFactory::class,
        TwoFactorAuthCardService::class => \TestSupport\Factory\TwoFactorAuthCardServiceFactory::class,
        AedmService::class => \TestSupport\Factory\AedmServiceFactory::class,
        DocumentService::class => \TestSupport\Factory\DocumentServiceFactory::class,
        GVTSTesterService::class => \TestSupport\Factory\GVTSTesterServiceFactory::class,
        StatisticsAmazonCacheService::class => \TestSupport\Factory\StatisticsAmazonCacheFactory::class,
        GdsSurveyService::class => GdsSurveyServiceFactory::class,
        OneHundredMotTestsService::class => TestSupportOneHundredMotTestsServiceFactory::class,
        SecurityQuestionsService::class => SecurityQuestionsServiceFactory::class,
        ClaimAccountService::class => ClaimAccountServiceFactory::class,
        CatUserService::class => CatUserServiceFactory::class,
        SiteRoleNominationService::class => SiteRoleNominationServiceFactory::class,
        OrganisationRoleNominationService::class => RoleNominationServiceFactory::class,
    ],
];
