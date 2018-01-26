<?php

use Application\Controller\FormsController;
use Application\Controller\PrivacyPolicyController;
use Application\Controller\ReportController;
use Application\Factory\Controller\ReportControllerFactory;
use Application\Controller\CookiesController;
use Application\Factory\FormsControllerFactory;
use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use DvsaMotEnforcement\Controller\MotTestController as EnforcementMotTestController;
use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use DvsaMotEnforcement\Controller\ReinspectionReportController;
use DvsaMotEnforcement\Factory\Controller\MotTestControllerFactory as EnforcementMotTestControllerFactory;
use DvsaMotEnforcement\Factory\MotTestSearchControllerFactory as EnforcementMotTestSearchControllerFactory;
use DvsaMotEnforcementApi\Controller\MotTestApiController;
use DvsaMotTest\Controller\BrakeTestResultsController;
use DvsaMotTest\Controller\CertificatePrintingController;
use DvsaMotTest\Controller\LocationSelectController;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Controller\RefuseToTestController;
use DvsaMotTest\Controller\ReplacementCertificateController;
use DvsaMotTest\Controller\SpecialNoticesController;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Controller\TesterMotTestLogController;
use DvsaMotTest\Controller\TestItemSelectorController;
use DvsaMotTest\Controller\VehicleDictionaryController;
use DvsaMotTest\Factory\Controller\CertificatePrintingControllerFactory;
use DvsaMotTest\Factory\Controller\MotTestControllerFactory;
use DvsaMotTest\Factory\Controller\RefuseToTestControllerFactory;
use DvsaMotTest\Factory\Controller\ReplacementCertificateControllerFactory;
use DvsaMotTest\Factory\Controller\SpecialNoticesControllerFactory;
use DvsaMotTest\Factory\Controller\StartTestConfirmationControllerFactory;
use DvsaMotTest\Factory\Controller\TesterMotTestLogControllerFactory;
use DvsaMotTest\Factory\Controller\BrakeTestResultsControllerFactory;

return [
    'invokables' => [
        CookiesController::class => CookiesController::class,
        PrivacyPolicyController::class => PrivacyPolicyController::class,
        LocationSelectController::class => LocationSelectController::class,
        VehicleDictionaryController::class => VehicleDictionaryController::class,
        TestItemSelectorController::class => TestItemSelectorController::class,
        MotTestApiController::class => MotTestApiController::class,
        ReinspectionReportController::class => ReinspectionReportController::class,
    ],
    'factories' => [
        BrakeTestResultsController::class => BrakeTestResultsControllerFactory::class,
        RefuseToTestController::class => RefuseToTestControllerFactory::class,
        SpecialNoticesController::class => SpecialNoticesControllerFactory::class,
        StartTestConfirmationController::class => StartTestConfirmationControllerFactory::class,
        EnforcementMotTestSearchController::class => EnforcementMotTestSearchControllerFactory::class,
        EnforcementMotTestController::class => EnforcementMotTestControllerFactory::class,
        TesterMotTestLogController::class => TesterMotTestLogControllerFactory::class,
        ReplacementCertificateController::class => ReplacementCertificateControllerFactory::class,
        CertificatePrintingController::class => CertificatePrintingControllerFactory::class,
        ReplacementCertificateController::class => ReplacementCertificateControllerFactory::class,
        FormsController::class => FormsControllerFactory::class,
        MotTestController::class => MotTestControllerFactory::class,
        ReportController::class => ReportControllerFactory::class,
    ],
    'abstract_factories' => [
        AutoWireFactory::class,
    ],
];
