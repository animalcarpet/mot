<?php

use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Configuration\MotConfigFactory;
use DvsaCommonApi\Transaction\ServiceTransactionAwareInitializer;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\TestItemCategoryRepository;
use DvsaMotApi\Factory\S3CsvStoreFactory;
use DvsaMotApi\Factory\CertificateReplacementRepositoryFactory;
use DvsaMotApi\Factory\ConfigurationRepositoryFactory;
use DvsaMotApi\Factory\Helper\RoleEventHelperFactory;
use DvsaMotApi\Factory\Helper\RoleNotificationHelperFactory;
use DvsaMotApi\Factory\Helper\TesterQualificationStatusChangeEventHelperFactory;
use DvsaMotApi\Factory\MotTestRepositoryFactory;
use DvsaMotApi\Factory\MotTestTypeRepositoryFactory;
use DvsaMotApi\Factory\OdometerReadingRepositoryFactory;
use DvsaMotApi\Factory\ReplacementCertificateDraftRepositoryFactory;
use DvsaMotApi\Factory\RfrRepositoryFactory;
use DvsaMotApi\Factory\Service\BrakeTestResultServiceFactory;
use DvsaMotApi\Factory\Service\CertificateChangeServiceFactory;
use DvsaMotApi\Factory\Service\CertificateCreationServiceFactory;
use DvsaMotApi\Factory\Service\CertificateExpiryServiceFactory;
use DvsaMotApi\Factory\Service\CreateMotTestServiceFactory;
use DvsaMotApi\Factory\Service\DemoTestAssessmentServiceFactory;
use DvsaMotApi\Factory\Service\EmergencyServiceFactory;
use DvsaMotApi\Factory\Service\EnforcementMotTestResultServiceFactory;
use DvsaMotApi\Factory\Service\EnforcementSiteAssessmentServiceFactory;
use DvsaMotApi\Factory\Service\Mapper\MotTestMapperFactory;
use DvsaMotApi\Factory\Service\MotTestCompareServiceFactory;
use DvsaMotApi\Factory\Service\MotTestDateHelperFactory;
use DvsaMotApi\Factory\Service\MotTestOptionsServiceFactory;
use DvsaMotApi\Factory\Service\MotTestReasonForRejectionServiceFactory;
use DvsaMotApi\Factory\Service\MotTestSecurityServiceFactory;
use DvsaMotApi\Factory\Service\MotTestServiceFactory;
use DvsaMotApi\Factory\Service\MotTestShortSummaryServiceFactory;
use DvsaMotApi\Factory\Service\MotTestStatusChangeNotificationFactory;
use DvsaMotApi\Factory\Service\MotTestStatusChangeServiceFactory;
use DvsaMotApi\Factory\Service\MotTestStatusServiceFactory;
use DvsaMotApi\Factory\Service\MotTestTypeServiceFactory;
use DvsaMotApi\Factory\Service\OdometerReadingQueryServiceFactory;
use DvsaMotApi\Factory\Service\OdometerReadingUpdatingServiceFactory;
use DvsaMotApi\Factory\Service\ReplacementCertificateDraftCreatorFactory;
use DvsaMotApi\Factory\Service\ReplacementCertificateDraftUpdaterFactory;
use DvsaMotApi\Factory\Service\ReplacementCertificateServiceFactory;
use DvsaMotApi\Factory\Service\ReplacementCertificateUpdaterFactory;
use DvsaMotApi\Factory\Service\TesterExpiryServiceFactory;
use DvsaMotApi\Factory\Service\TesterMotTestLogServiceFactory;
use DvsaMotApi\Factory\Service\TesterSearchServiceFactory;
use DvsaMotApi\Factory\Service\TesterServiceFactory;
use DvsaMotApi\Factory\Service\TestingOutsideOpeningHoursNotificationServiceFactory;
use DvsaMotApi\Factory\Service\TestItemSelectorServiceFactory;
use DvsaMotApi\Factory\Service\UserServiceFactory;
use DvsaMotApi\Factory\Service\Validator\MotTestChangeValidatorFactory;
use DvsaMotApi\Factory\Service\Validator\MotTestValidatorFactory;
use DvsaMotApi\Factory\Service\Validator\OdometerReadingDeltaAnomalyCheckerFactory;
use DvsaMotApi\Factory\Service\Validator\ReplacementCertificateDraftChangeValidatorFactory;
use DvsaMotApi\Factory\Service\Validator\RetestEligibilityValidatorFactory;
use DvsaMotApi\Factory\Service\VehicleHistoryServiceFactory;
use DvsaMotApi\Factory\Service\VehicleServiceFactory;
use DvsaMotApi\Factory\Service\SurveyServiceFactory;
use DvsaMotApi\Factory\TestItemCategoryRepositoryFactory;
use DvsaMotApi\Factory\VehicleRepositoryFactory;
use DvsaMotApi\Helper\RoleEventHelper;
use DvsaMotApi\Helper\RoleNotificationHelper;
use DvsaMotApi\Helper\TesterQualificationStatusChangeEventHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\CreateMotTestService;
use DvsaMotApi\Service\DemoTestAssessmentService;
use DvsaMotApi\Service\EmergencyService;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestOptionsService;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\MotTestStatusChangeNotificationService;
use DvsaMotApi\Service\S3\S3CsvStore;
use DvsaMotApi\Service\SurveyService;
use DvsaMotApi\Service\TesterMotTestLogService;
use DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService;
use DvsaMotApi\Service\UserService;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use DvsaMotApi\Service\Validator\BrakeTestResultValidator;
use DvsaMotApi\Service\Validator\MotTestStatusChangeValidator;
use DvsaMotApi\Service\Validator\ReplacementCertificateDraftChangeValidator;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
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
        UserService::class                                   => UserServiceFactory::class,
        'VehicleService'                                     => VehicleServiceFactory::class,
        EmergencyService::class                              => EmergencyServiceFactory::class,
        'EnforcementMotTestResultService'                    => EnforcementMotTestResultServiceFactory::class,
        'EnforcementSiteAssessmentService'                   => EnforcementSiteAssessmentServiceFactory::class,
        'TestItemSelectorService'                            => TestItemSelectorServiceFactory::class,
        'TesterService'                                      => TesterServiceFactory::class,
        'TesterSearchService'                                => TesterSearchServiceFactory::class,
        'TesterExpiryService'                                => TesterExpiryServiceFactory::class,
        'BrakeTestResultService'                             => BrakeTestResultServiceFactory::class,
        'MotTestSecurityService'                             => MotTestSecurityServiceFactory::class,
        CreateMotTestService::class                          => CreateMotTestServiceFactory::class,
        'MotTestService'                                     => MotTestServiceFactory::class,
        'MotTestShortSummaryService'                         => MotTestShortSummaryServiceFactory::class,
        'MotTestStatusService'                               => MotTestStatusServiceFactory::class,
        'MotTestStatusChangeService'                         => MotTestStatusChangeServiceFactory::class,
        MotTestStatusChangeNotificationService::class        => MotTestStatusChangeNotificationFactory::class,
        TestingOutsideOpeningHoursNotificationService::class => TestingOutsideOpeningHoursNotificationServiceFactory::class,
        MotTestDateHelperService::class                      => MotTestDateHelperFactory::class,
        'TestSlotTransactionService'                         => TestSlotTransactionServiceFactory::class,
        'MotTestTypeService'                                 => MotTestTypeServiceFactory::class,
        'MotTestCompareService'                              => MotTestCompareServiceFactory::class,
        'MotTestValidator'                                   => MotTestValidatorFactory::class,
        MotTestStatusChangeValidator::class                  => MotTestChangeValidatorFactory::class,
        MotTestRepository::class                             => MotTestRepositoryFactory::class,
        'MotTestTypeRepository'                              => MotTestTypeRepositoryFactory::class,
        RetestEligibilityValidator::class                    => RetestEligibilityValidatorFactory::class,
        'VehicleRepository'                                  => VehicleRepositoryFactory::class,
        'CertificateExpiryService'                           => CertificateExpiryServiceFactory::class,
        'ConfigurationRepository'                            => ConfigurationRepositoryFactory::class,
        'RfrRepository'                                      => RfrRepositoryFactory::class,
        'OdometerReadingDeltaAnomalyChecker'                 => OdometerReadingDeltaAnomalyCheckerFactory::class,
        'OdometerReadingRepository'                          => OdometerReadingRepositoryFactory::class,
        'OdometerReadingUpdatingService'                     => OdometerReadingUpdatingServiceFactory::class,
        'OdometerReadingQueryService'                        => OdometerReadingQueryServiceFactory::class,
        'MotTestMapper'                                      => MotTestMapperFactory::class,
        //  @ARCHIVE VM-4532    MotDemoTestService
        CertificateCreationService::class                   => CertificateCreationServiceFactory::class,
        'CertificateReplacementRepository'                  => CertificateReplacementRepositoryFactory::class,
        'ReplacementCertificateUpdater'                     => ReplacementCertificateUpdaterFactory::class,
        'ReplacementCertificateDraftRepository'             => ReplacementCertificateDraftRepositoryFactory::class,
        'ReplacementCertificateDraftCreator'                => ReplacementCertificateDraftCreatorFactory::class,
        'ReplacementCertificateDraftUpdater'                => ReplacementCertificateDraftUpdaterFactory::class,
        'ReplacementCertificateService'                     => ReplacementCertificateServiceFactory::class,
        'CertificateChangeService'                          => CertificateChangeServiceFactory::class,
        MotTestReasonForRejectionService::class             => MotTestReasonForRejectionServiceFactory::class,
        TestItemCategoryRepository::class                   => TestItemCategoryRepositoryFactory::class,
        VehicleHistoryService::class                        => VehicleHistoryServiceFactory::class,
        MotTestOptionsService::class                        => MotTestOptionsServiceFactory::class,
        DemoTestAssessmentService::class                    => DemoTestAssessmentServiceFactory::class,
        TesterQualificationStatusChangeEventHelper::class   => TesterQualificationStatusChangeEventHelperFactory::class,
        TesterMotTestLogService::class                      => TesterMotTestLogServiceFactory::class,
        RoleEventHelper::class                              => RoleEventHelperFactory::class,
        RoleNotificationHelper::class                       => RoleNotificationHelperFactory::class,
        SurveyService::class                                => SurveyServiceFactory::class,
        MotConfig::class                                    => MotConfigFactory::class,
        ReplacementCertificateDraftChangeValidator::class   => ReplacementCertificateDraftChangeValidatorFactory::class,
        S3CsvStore::class                                   => S3CsvStoreFactory::class,
    ],
];
