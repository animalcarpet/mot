<?php

use DvsaCommonApi\Transaction\ServiceTransactionAwareInitializer;
use DvsaEntities\Repository\TestItemCategoryRepository;
use DvsaMotApi\Factory\Helper\RoleEventHelperFactory;
use DvsaMotApi\Factory\Helper\RoleNotificationHelperFactory;
use DvsaMotApi\Factory\Helper\TesterQualificationStatusChangeEventHelperFactory;
use DvsaMotApi\Factory\Service\CertificatePdfServiceFactory;
use DvsaMotApi\Factory\Service\DemoTestAssessmentServiceFactory;
use DvsaMotApi\Factory\Service\MotTestOptionsServiceFactory;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaMotApi\Factory\Service\MotTestRecentCertificateServiceFactory;
use DvsaMotApi\Factory\Service\TesterMotTestLogServiceFactory;
use DvsaMotApi\Factory\Service\Validator\RetestEligibilityValidatorFactory;
use DvsaMotApi\Factory\Service\VehicleHistoryServiceFactory;
use DvsaMotApi\Factory\TestItemCategoryRepositoryFactory;
use DvsaMotApi\Helper\RoleEventHelper;
use DvsaMotApi\Helper\RoleNotificationHelper;
use DvsaMotApi\Helper\TesterQualificationStatusChangeEventHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\CertificatePdfService;
use DvsaMotApi\Service\CreateMotTestService;
use DvsaMotApi\Service\DemoTestAssessmentService;
use DvsaMotApi\Service\EmergencyService;
use DvsaMotApi\Service\EmergencyServiceFactory;
use DvsaMotApi\Service\MotTestDateHelper;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestOptionsService;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\MotTestRecentCertificateService;
use DvsaMotApi\Service\MotTestStatusChangeNotificationService;
use DvsaMotApi\Service\TesterMotTestLogService;
use DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService;
use DvsaMotApi\Service\UserService;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use DvsaMotApi\Service\Validator\BrakeTestResultValidator;
use DvsaMotApi\Service\Validator\MotTestStatusChangeValidator;
use DvsaMotApi\Service\VehicleHistoryService;

return [
    'invokables' => [
        'BrakeTestConfigurationValidator'                   => BrakeTestConfigurationValidator::class,
        'BrakeTestResultValidator'                          => BrakeTestResultValidator::class
    ],
    'initializers' => [
        'transactionAware'                                  => ServiceTransactionAwareInitializer::class,
    ],
    'factories'  => [
        UserService::class                                   => \DvsaMotApi\Factory\Service\UserServiceFactory::class,
        'VehicleService'                                     => \DvsaMotApi\Factory\Service\VehicleServiceFactory::class,
        EmergencyService::class                              => \DvsaMotApi\Factory\Service\EmergencyServiceFactory::class,
        'EnforcementMotTestResultService'                    => \DvsaMotApi\Factory\Service\EnforcementMotTestResultServiceFactory::class,
        'EnforcementSiteAssessmentService'                   => \DvsaMotApi\Factory\Service\EnforcementSiteAssessmentServiceFactory::class,
        'TestItemSelectorService'                            => \DvsaMotApi\Factory\Service\TestItemSelectorServiceFactory::class,
        'TesterService'                                      => \DvsaMotApi\Factory\Service\TesterServiceFactory::class,
        'TesterSearchService'                                => \DvsaMotApi\Factory\Service\TesterSearchServiceFactory::class,
        'TesterExpiryService'                                => \DvsaMotApi\Factory\Service\TesterExpiryServiceFactory::class,
        'BrakeTestResultService'                             => \DvsaMotApi\Factory\Service\BrakeTestResultServiceFactory::class,
        'MotTestSecurityService'                             => \DvsaMotApi\Factory\Service\MotTestSecurityServiceFactory::class,
        CreateMotTestService::class                          => \DvsaMotApi\Factory\Service\CreateMotTestServiceFactory::class,
        'MotTestService'                                     => \DvsaMotApi\Factory\Service\MotTestServiceFactory::class,
        'MotTestShortSummaryService'                         => \DvsaMotApi\Factory\Service\MotTestShortSummaryServiceFactory::class,
        'MotTestStatusService'                               => \DvsaMotApi\Factory\Service\MotTestStatusServiceFactory::class,
        'MotTestStatusChangeService'                         => \DvsaMotApi\Factory\Service\MotTestStatusChangeServiceFactory::class,
        MotTestStatusChangeNotificationService::class        => \DvsaMotApi\Factory\Service\MotTestStatusChangeNotificationFactory::class,
        TestingOutsideOpeningHoursNotificationService::class => \DvsaMotApi\Factory\Service\TestingOutsideOpeningHoursNotificationServiceFactory::class,
        MotTestDateHelperService::class                      => \DvsaMotApi\Factory\Service\MotTestDateHelperFactory::class,
        'TestSlotTransactionService'                         => \DvsaMotApi\Factory\Service\TestSlotTransactionServiceFactory::class,
        'MotTestTypeService'                                 => \DvsaMotApi\Factory\Service\MotTestTypeServiceFactory::class,
        'MotTestCompareService'                              => \DvsaMotApi\Factory\Service\MotTestCompareServiceFactory::class,
        'MotTestValidator'                                   => \DvsaMotApi\Factory\Service\Validator\MotTestValidatorFactory::class,
        MotTestStatusChangeValidator::class                  => \DvsaMotApi\Factory\Service\Validator\MotTestChangeValidatorFactory::class,
        'MotTestRepository'                                  => \DvsaMotApi\Factory\MotTestRepositoryFactory::class,
        'MotTestTypeRepository'                              => \DvsaMotApi\Factory\MotTestTypeRepositoryFactory::class,
        RetestEligibilityValidator::class                    => RetestEligibilityValidatorFactory::class,
        'VehicleRepository'                                  => \DvsaMotApi\Factory\VehicleRepositoryFactory::class,
        'CertificateExpiryService'                           => \DvsaMotApi\Factory\Service\CertificateExpiryServiceFactory::class,
        'ConfigurationRepository'                            => \DvsaMotApi\Factory\ConfigurationRepositoryFactory::class,
        'RfrRepository'                                      => \DvsaMotApi\Factory\RfrRepositoryFactory::class,
        'OdometerReadingDeltaAnomalyChecker'                 => \DvsaMotApi\Factory\Service\Validator\OdometerReadingDeltaAnomalyCheckerFactory::class,
        'OdometerReadingRepository'                          => \DvsaMotApi\Factory\OdometerReadingRepositoryFactory::class,
        'OdometerReadingUpdatingService'                     => \DvsaMotApi\Factory\Service\OdometerReadingUpdatingServiceFactory::class,
        'OdometerReadingQueryService'                        => \DvsaMotApi\Factory\Service\OdometerReadingQueryServiceFactory::class,
        'RoleRefreshService'                                 => \DvsaMotApi\Factory\Service\RoleRefreshServiceFactory::class,
        'MotTestMapper'                                      => \DvsaMotApi\Factory\Service\Mapper\MotTestMapperFactory::class,
        //  @ARCHIVE VM-4532    MotDemoTestService
        CertificateCreationService::class                   => \DvsaMotApi\Factory\Service\CertificateCreationServiceFactory::class,
        'CertificateReplacementRepository'                  => \DvsaMotApi\Factory\CertificateReplacementRepositoryFactory::class,
        'ReplacementCertificateUpdater'                     => \DvsaMotApi\Factory\Service\ReplacementCertificateUpdaterFactory::class,
        'ReplacementCertificateDraftRepository'             => \DvsaMotApi\Factory\ReplacementCertificateDraftRepositoryFactory::class,
        'ReplacementCertificateDraftCreator'                => \DvsaMotApi\Factory\Service\ReplacementCertificateDraftCreatorFactory::class,
        'ReplacementCertificateDraftUpdater'                => \DvsaMotApi\Factory\Service\ReplacementCertificateDraftUpdaterFactory::class,
        'ReplacementCertificateService'                     => \DvsaMotApi\Factory\Service\ReplacementCertificateServiceFactory::class,
        'CertificateChangeService'                          => \DvsaMotApi\Factory\Service\CertificateChangeServiceFactory::class,
        MotTestReasonForRejectionService::class             => \DvsaMotApi\Factory\Service\MotTestReasonForRejectionServiceFactory::class,
        TestItemCategoryRepository::class                   => TestItemCategoryRepositoryFactory::class,
        VehicleHistoryService::class                        => VehicleHistoryServiceFactory::class,
        MotTestOptionsService::class                        => MotTestOptionsServiceFactory::class,
        DemoTestAssessmentService::class                    => DemoTestAssessmentServiceFactory::class,
        TesterQualificationStatusChangeEventHelper::class   => TesterQualificationStatusChangeEventHelperFactory::class,
        TesterMotTestLogService::class                      => TesterMotTestLogServiceFactory::class,
        RoleEventHelper::class                              => RoleEventHelperFactory::class,
        RoleNotificationHelper::class                       => RoleNotificationHelperFactory::class
    ],
];
