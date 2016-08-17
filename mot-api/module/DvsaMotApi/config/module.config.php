<?php

use DvsaCommon\Validator\UsernameValidator;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Controller\ContingencyTestController;
use DvsaMotApi\Controller\DemoTestAssessmentController;
use DvsaMotApi\Controller\IdentityDataController;
use DvsaMotApi\Controller\MotCertificateEmailController;
use DvsaMotApi\Controller\MotCertificatePdfController;
use DvsaMotApi\Controller\MotCertificatesController;
use DvsaMotApi\Controller\MotTestController;
use DvsaMotApi\Controller\MotTestOptionsController;
use DvsaMotApi\Controller\MotTestSearchController;
use DvsaMotApi\Controller\MotTestShortSummaryController;
use DvsaMotApi\Controller\MotTestStatusController;
use DvsaMotApi\Controller\ReasonForRejectionController;
use DvsaMotApi\Controller\ReplacementCertificateDraftController;
use DvsaMotApi\Controller\RetestController;
use DvsaMotApi\Controller\SurveyController;
use DvsaMotApi\Controller\TesterMotTestLogController;
use DvsaMotApi\Controller\TestItemCategoryNameController;

return [
    'controllers' => include 'controllers.config.php',
    UsernameValidator::class => [
        'options' => [
            'max' => Person::FIELD_USERNAME_LENGTH,
        ],
    ],
    'router'          => [
        'routes' => [
            'emergency-log' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/emergency-log',
                    'defaults'    => [
                        'controller' => ContingencyTestController::class,
                    ],
                ],
            ],
            'certificate-print' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/certificate-print/:id[/:dupmode]',
                    'constraints' => [
                        'dupmode' => 'dup',
                    ],
                    'defaults'    => [
                        'controller' => \DvsaMotApi\Controller\CertificatePrintingController::class,
                        'action'     => 'print',
                    ],
                ],
            ],
            'pdf-certificate-print' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/pdf-certificate-print',
                    'defaults'    => [
                        'controller' => \DvsaMotApi\Controller\CertificatePrintingController::class,
                        'action'     => 'getAmazonPdf',
                    ],
                ],
            ],
            'print-report'                   => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/print-report/:docId',
                    'constraints' => [
                        'docId' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => \DvsaMotApi\Controller\CertificatePrintingController::class,
                        'action'     => 'printByDocId',
                    ],
                ],
            ],
            'contingency-print' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/contingency-print/:name',
                    'constraints' => [
                        'name' => '(CT20|CT30|CT32)',
                    ],
                    'defaults'    => [
                        'controller' => \DvsaMotApi\Controller\CertificatePrintingController::class,
                        'action'     => 'printContingency',
                    ],
                ],
            ],
            'reinspection-outcome'            => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/reinspection-outcome',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\ReinspectionReport',
                    ],
                ],
                'may_terminate' => true,
            ],
            'inspection-location'                          => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/inspection-location',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\InspectionLocation',
                    ],
                ],
                'may_terminate' => true,
            ],
            'index'                          => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\Index',
                    ],
                ],
            ],
            'session'                        => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/session[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => 'DvsaMotApi\Controller\Session',
                    ],
                ],
            ],
            'identity-data'                        => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/identity-data',

                    'defaults'    => [
                        'controller' => IdentityDataController::class,
                    ],
                ],
            ],
            'user'                           => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/user[/:username]',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\User',
                    ],
                ],
            ],
            'mot-retest'                     => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/mot-retest[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => RetestController::class,
                    ],
                ],
            ],
            'mot-test-refusal'               => [
                'type'          => 'Segment',
                'options'       => [
                    'route'       => '/mot-test-refusal',
                    'defaults'    => [
                        'controller' => 'DvsaMotApi\Controller\MotTestRefusal',
                    ],
                ],
            ],
            'mot-demo-test' => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/mot-demo-test[/:id]',
                    'constraints' => [
                        'id' => '[0-9a-zA-Z]+',
                    ],
                    'defaults'    => [
                        'controller' => 'DvsaMotApi\Controller\DemoTest',
                    ],
                ],
            ],
            'demo-test-assessment' => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/person/:personId/demo-test-assessment',
                    'defaults'    => [
                        'controller' => DemoTestAssessmentController::class,
                    ],
                ],
            ],
            'mot-test'                       => [
                'type'          => 'Segment',
                'options'       => [
                    'route'       => '/mot-test[/:motTestNumber]',
                    'constraints' => [
                        'motTestNumber' => \DvsaCommon\Constants\MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => MotTestController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'minimal-mot' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/minimal',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'getMinimalMot',
                            ],
                        ],
                    ],
                    'find-mot-test-number' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/find-mot-test-number',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'findMotTestNumber',
                            ],
                        ],
                    ],
                    'test-item-selector'      => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/test-item-selector/:tisId',
                            'constraints' => [
                                'tisId' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => 'DvsaMotApi\Controller\TestItemSelector',
                            ],
                        ],
                    ],
                    'test-item-category-name' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/test-item-category-name',
                            'defaults' => [
                                'controller' => TestItemCategoryNameController::class,
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'reason-for-rejection'  => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/reason-for-rejection',
                            'defaults' => [
                                'controller' => ReasonForRejectionController::class,
                            ],
                        ],
                    ],
                    'short-summary'      => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/short-summary',
                            'defaults'    => [
                                'controller' => MotTestShortSummaryController::class,
                            ],
                        ],
                    ],
                    'reasons-for-rejection'   => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/reasons-for-rejection[/:motTestRfrId]',
                            'constraints' => [
                                'motTestRfrId' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => 'DvsaMotApi\Controller\MotTestReasonForRejection',
                            ],
                        ],
                    ],
                    'brake-test-result'       => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/brake-test-result',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\MotTestBrakeTestResult',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'validate-configuration' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/validate-configuration',
                                    'defaults' => [
                                        'controller' => 'DvsaMotApi\Controller\MotTestBrakeTestConfigurationValidation',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'odometer'                => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/odometer-reading',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\MotTestOdometer',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'reading-notices' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/notices',
                                    'defaults' => [
                                        'controller' => 'DvsaMotApi\Controller\MotTestOdometer',
                                        'action'     => 'getNotices',
                                    ],
                                ],
                            ],
                            'modify-check'    => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/modify-check',
                                    'defaults' => [
                                        'controller' => 'DvsaMotApi\Controller\MotTestOdometer',
                                        'action'     => 'canModifyOdometer',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'status'                  => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/status',
                            'defaults' => [
                                'controller' => MotTestStatusController::class,
                            ],
                        ],
                    ],
                    'compare'                 => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/compare',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\MotTestCompare',
                            ],
                        ],
                    ],
                    'certificate-details'     => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/certificate-details[/:variation]',
                            'constraints' => [
                                'variation' => '[^/]+',
                            ],
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'getCertificateDetails',
                            ],
                        ],
                    ],
                    'options'                 => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/options',
                            'defaults' => [
                                'controller' => MotTestOptionsController::class,
                            ],
                        ],
                    ],
                    'replacement-certificate-draft'  => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/replacement-certificate-draft[/:id]',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => ReplacementCertificateDraftController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'replacement-certificate-draft-apply' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/apply',
                                    'defaults' => [
                                        'controller' => ReplacementCertificateDraftController::class,
                                        'action'     => 'apply',
                                    ],
                                ],
                            ],
                            'replacement-certificate-draft-diff'  => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/diff',
                                    'defaults' => [
                                        'controller' => ReplacementCertificateDraftController::class,
                                        'action'     => 'diff',
                                    ],
                                ],
                            ],
                        ],

                    ],
                ],
            ],
            'recent-mot-certificate' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/mot-recent-certificate[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => MotCertificatesController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'pdf-certificate-link' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/pdf-link',
                            'defaults' => [
                                'controller' => MotCertificatePdfController::class,
                            ],
                        ],
                    ],
                    'email-certificate' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/email',
                            'defaults' => [
                                'controller' => MotCertificateEmailController::class,
                            ],
                        ],
                    ],
                ],
            ],
            'mot-test-validate-retest' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/mot-retest-validate/:motTestNumber',
                    'defaults' => [
                        'controller' => MotTestController::class,
                        'action'     => 'validateMOTRetest',
                    ],
                ],
            ],
            'mot-test-compare'           => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/mot-test/compare',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\MotTestCompare',
                        'action'     => 'compareMotTest',
                    ],
                ],
            ],

            'mot-test-cert-number'           => [
                'type'    => 'Segment',
                'options'       => [
                    'route'       => '/mot-test-certificate',
                    'defaults'    => [
                        'controller' => MotTestController::class,
                        'action'     => 'getMotTestByNumber',
                    ],
                ],
            ],
            'mot-test-search'                => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/mot-test-search',
                    'defaults' => [
                        'controller' => MotTestSearchController::class,
                        'action'     => 'getTests',
                    ],
                ],
            ],
            'cert-change-diff-tester-reason' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/cert-change-diff-tester-reason',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\CertChangeDiffTesterReason',
                    ],
                ],
            ],
            'tester'                         => [
                'type'          => 'Segment',
                'options'       => [
                    'route'       => '/tester[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => 'DvsaMotApi\Controller\Tester',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'full' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/full',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\TesterSearch',
                            ],
                        ],
                    ],
                    'in-progress-test-id' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/in-progress-test-id',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\Tester',
                                'action' => 'getInProgressTestId',
                            ],
                        ],
                    ],
                    'vehicle-testing-stations' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/vehicle-testing-stations',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\Tester',
                                'action'     => 'getVehicleTestingStations',
                            ],
                        ],
                    ],
                    'vts-slot-balance' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/vts-slot-balance',
                            'defaults' => [
                                'controller' => 'DvsaMotApi\Controller\Tester',
                                'action'     => 'getVtsWithSlotBalance',
                            ],
                        ],
                    ],
                    'tester-mot-test-log' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/mot-test-log',
                            'defaults' => [
                                'controller' => TesterMotTestLogController::class,
                                'action'     => 'logData',
                            ],
                        ],
                    ],
                    'tester-mot-test-log-summary' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/mot-test-log/summary',
                            'defaults' => [
                                'controller' => TesterMotTestLogController::class,
                                'action'     => 'summary',
                            ],
                        ],
                    ],
                ],
            ],
            'vehicle'                        => [
                'type'          => 'Segment',
                'options'       => [
                    'route'       => '/vehicle',
                    'defaults'    => [
                        'controller' => 'DvsaMotApi\Controller\Vehicle',
                    ],
                ],
            ],
            'tester-expiry'       => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/tester-expiry',
                    'defaults' => [
                        'controller' => 'DvsaMotApi\Controller\TesterExpiry',
                    ],
                ],
            ],

            //  @ARCHIVE VM-4532    route to enforcement/mot-demo-test

            'enforcement-mot-test-result'    => [
                'type'    => 'Segment',
                'options' => [
                    'route'       => '/enforcement-mot-test-result[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => 'DvsaMotApi\Controller\EnforcementMotTestResult',
                    ],
                ],
            ],
            'survey' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/survey',
                    'defaults' => [
                        'controller' => SurveyController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'report' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/reports',
                            'defaults' => [
                                'controller' => SurveyController::class,
                                'action' => 'getReports',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'generate' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/generate',
                                    'defaults' => [
                                        'controller' => SurveyController::class,
                                        'action' => 'generateReports',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'should-display' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/should-display',
                            'defaults' => [
                                'controller' => SurveyController::class,
                                'action' => 'shouldDisplaySurvey',
                            ],
                        ],
                    ],
                    'token' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/token',
                            'defaults' => [
                                'controller' => SurveyController::class,
                                'action' => 'createSessionToken',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'validate' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/validate',
                                    'defaults' => [
                                        'controller' => SurveyController::class,
                                        'action' => 'validateToken',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],//end of 'routes =>'
    ], // end of 'router =>'
    'service_manager' => [
        'aliases' => [
            'translator' => 'MvcTranslator',
        ],
    ],
    'translator'      => [
        'locale'                    => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];
