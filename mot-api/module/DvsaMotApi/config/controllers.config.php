<?php

use DvsaCommonApi\Transaction\ControllerTransactionAwareInitializer;
use DvsaMotApi\Controller\CertificatePrintingController;
use DvsaMotApi\Controller\MotCertificateEmailController;
use DvsaMotApi\Controller\MotCertificatePdfController;
use DvsaMotApi\Controller\MotCertificatesController;
use DvsaMotApi\Factory\Controller\CertificatePrintingControllerFactory;
use DvsaMotApi\Controller\IdentityDataController;
use DvsaMotApi\Controller\MotTestController;
use DvsaMotApi\Controller\MotTestOptionsController;
use DvsaMotApi\Controller\MotTestSearchController;
use DvsaMotApi\Controller\MotTestShortSummaryController;
use DvsaMotApi\Controller\MotTestStatusController;
use DvsaMotApi\Controller\ReasonForRejectionController;
use DvsaMotApi\Controller\ReplacementCertificateDraftController;
use DvsaMotApi\Controller\RetestController;
use DvsaMotApi\Controller\TestItemCategoryNameController;
use DvsaMotApi\Controller\TesterMotTestLogController;
use DvsaMotApi\Factory\Controller\MotCertificateEmailControllerFactory;
use DvsaMotApi\Factory\Controller\MotCertificatePdfControllerFactory;
use DvsaMotApi\Factory\Controller\MotCertificatesControllerFactory;
use DvsaMotApi\Factory\Controller\MotTestStatusControllerFactory;
use DvsaMotApi\Factory\Controller\TesterControllerFactory;
use DvsaMotApi\Factory\Controller\UserControllerFactory;
use DvsaMotApi\Factory\Controller\TesterMotTestLogControllerFactory;
use DvsaMotApi\Controller\DemoTestAssessmentController;
use DvsaMotApi\Factory\Controller\DemoTestAssessmentControllerFactory;
use DvsaMotApi\Factory\Controller\ReplacementCertificateDraftControllerFactory;


return [
    'initializers' => [
        'transactionAware' => ControllerTransactionAwareInitializer::class
    ],
    'invokables' => [
        'DvsaMotApi\Controller\Session' => \DvsaMotApi\Controller\SessionController::class,
        'DvsaMotApi\Controller\Index' => \DvsaMotApi\Controller\IndexController::class,
        RetestController::class => RetestController::class,
        MotTestController::class => MotTestController::class,
        'DvsaMotApi\Controller\MotTestRefusal' => \DvsaMotApi\Controller\MotTestRefusalController::class,
        'DvsaMotApi\Controller\DemoTest' => \DvsaMotApi\Controller\DemoTestController::class,
        'DvsaMotApi\Controller\TesterExpiry' => \DvsaMotApi\Controller\TesterExpiryController::class,
        'DvsaMotApi\Controller\Vehicle' => \DvsaMotApi\Controller\VehicleController::class,
        'DvsaMotApi\Controller\TesterSearch' => \DvsaMotApi\Controller\TesterSearchController::class,
        'DvsaMotApi\Controller\TestItemSelector' => \DvsaMotApi\Controller\TestItemSelectorController::class,
        TestItemCategoryNameController::class => TestItemCategoryNameController::class,
        ReasonForRejectionController::class => ReasonForRejectionController::class,
        'DvsaMotApi\Controller\MotTestOdometer' => \DvsaMotApi\Controller\MotTestOdometerController::class,
        'DvsaMotApi\Controller\MotTestCompare' => \DvsaMotApi\Controller\MotTestCompareController::class,
        MotTestSearchController::class => MotTestSearchController::class,
        'DvsaMotApi\Controller\EnforcementMotTestResult' =>
            \DvsaMotApi\Controller\EnforcementMotTestResultController::class,
        'DvsaMotApi\Controller\MotTestReasonForRejection' =>
            \DvsaMotApi\Controller\MotTestReasonForRejectionController::class,
        'DvsaMotApi\Controller\MotTestBrakeTestResult' =>
            \DvsaMotApi\Controller\MotTestBrakeTestResultController::class,
        'DvsaMotApi\Controller\MotTestBrakeTestConfigurationValidation' =>
            \DvsaMotApi\Controller\MotTestBrakeTestConfigurationValidationController::class,
        'DvsaMotApi\Controller\CertChangeDiffTesterReason' =>
            \DvsaMotApi\Controller\CertChangeDiffTesterReasonController::class,
        'DvsaMotApi\Controller\InspectionLocation' => \DvsaMotApi\Controller\InspectionLocationController::class,
        'DvsaMotApi\Controller\ReinspectionReport' => \DvsaMotApi\Controller\ReinspectionReportController::class,
        MotTestShortSummaryController::class => MotTestShortSummaryController::class,
        'DvsaMotApi\Controller\EmergencyLogController' => \DvsaMotApi\Controller\EmergencyLogController::class,
        MotTestOptionsController::class => MotTestOptionsController::class,
        IdentityDataController::class => IdentityDataController::class,
    ],
    'factories' => [
        CertificatePrintingController::class => CertificatePrintingControllerFactory::class,
        'DvsaMotApi\Controller\Tester' => TesterControllerFactory::class,
        'DvsaMotApi\Controller\User'   => UserControllerFactory::class,
        MotTestStatusController::class => MotTestStatusControllerFactory::class,
        DemoTestAssessmentController::class => DemoTestAssessmentControllerFactory::class,
        TesterMotTestLogController::class => TesterMotTestLogControllerFactory::class,
        ReplacementCertificateDraftController::class => ReplacementCertificateDraftControllerFactory::class,
        MotCertificatesController::class => MotCertificatesControllerFactory::class,
        MotCertificatePdfController::class => MotCertificatePdfControllerFactory::class,
        MotCertificateEmailController::class => MotCertificateEmailControllerFactory::class,
    ]
];