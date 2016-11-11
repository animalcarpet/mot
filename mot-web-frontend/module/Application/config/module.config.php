<?php

use Application\Controller as Application;
use Application\Navigation\Breadcrumbs\Handler\OrganisationNameBySiteResolver;
use Application\Navigation\Breadcrumbs\Handler\SimpleResolver;
use Application\Navigation\Breadcrumbs\Handler\SiteNameResolver;
use Application\View\Helper\CamelCaseToFirstUppercaseReadable;
use Application\View\Helper\CamelCaseToReadable;
use Application\View\HelperFactory\AuthorisationHelperFactory;
use Application\View\HelperFactory\CanTestWithoutOtpFactory;
use Application\View\HelperFactory\CurrentMotTestFactory;
use Application\View\HelperFactory\DashboardDataProviderFactory;
use Application\View\HelperFactory\GetSiteCountFactory;
use Application\View\HelperFactory\GetSitesFactory;
use Application\View\HelperFactory\IdentityHelperFactory;
use Application\View\HelperFactory\LocationSelectorFactory;
use Application\View\HelperFactory\ManualsAndGuidesFactory;
use Application\View\HelperFactory\ResourcesOnGovUkFactory;
use Dvsa\Mot\Frontend\Plugin\AjaxResponsePlugin;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaCommon\Constants\MotTestNumberConstraint;
use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use DvsaMotEnforcement\Controller as Enforcement;
use DvsaMotEnforcement\Controller\MotTestController as EnforcementMotTestController;
use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use DvsaMotEnforcementApi\Controller as Ajax;
use DvsaMotTest\Controller as MotTest;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Factory\Service\MotChecklistPdfServiceFactory;
use DvsaMotTest\Form\Validator\SpecialNoticePublishDateValidator;
use DvsaMotTest\NewVehicle\Controller\CreateVehicleController;
use DvsaMotTest\Service\MotChecklistPdfService;
use Zend\EventManager\EventManager;
use Application\Service\CanTestWithoutOtpService;
use Application\Factory\Service\CanTestWithoutOtpServiceFactory;
use DvsaMotTest\View\VehicleSearchResult\NonMotTestUrlTemplate;

return [
    'controllers' => require __DIR__ . '/controllers.config.php',
    'router'                     => [
        'routes' => [
            'forms'                                       => [
                'type'          => 'segment',
                'options'       => [
                    'route'    => '/forms',
                    'defaults' => [
                        'controller' => Application\FormsController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'contingency-pass-certificate'     => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/contingency-pass-certificate',
                            'defaults' => [
                                'controller' => Application\FormsController::class,
                                'action'     => 'contingencyPassCertificate',
                            ],
                        ],
                    ],
                    'contingency-fail-certificate'     => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/contingency-fail-certificate',
                            'defaults' => [
                                'controller' => Application\FormsController::class,
                                'action'     => 'contingencyFailCertificate',
                            ],
                        ],
                    ],
                    'contingency-advisory-certificate' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/contingency-advisory-certificate',
                            'defaults' => [
                                'controller' => Application\FormsController::class,
                                'action'     => 'contingencyAdvisoryCertificate',
                            ],
                        ],
                    ],
                ],
            ],
            'special-notices'                             => [
                'type'          => 'segment',
                'options'       => [
                    'route'    => '/special-notices',
                    'defaults' => [
                        'controller' => MotTest\SpecialNoticesController::class,
                        'action'     => 'displaySpecialNotices',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'all'     => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/all',
                            'defaults' => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'displayAllSpecialNotices',
                            ],
                        ],
                    ],
                    'print'   => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/print/:id[:extension]',
                            'constraints' => [
                                'id'        => '[0-9]+',
                                'extension' => '.[a-z]{3}',
                            ],
                            'defaults'    => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'printSpecialNotice',
                            ],
                        ],
                    ],
                    'removed' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/removed',
                            'defaults' => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'displayRemovedSpecialNotices',
                            ],
                        ],
                    ],
                    'remove'  => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:id/remove',
                            'defaults' => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'removeSpecialNotice',
                            ],
                        ],
                    ],
                    'create'  => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/create',
                            'defaults' => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'createSpecialNotice',
                            ],
                        ],
                    ],
                    'edit'    => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:id/edit',
                            'defaults' => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'edit',
                            ],
                        ],
                    ],
                    'preview' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:id/preview',
                            'defaults' => [
                                'controller' => MotTest\SpecialNoticesController::class,
                                'action'     => 'previewSpecialNotice',
                            ],
                        ],
                    ],
                ],
            ],
            'special-notice-acknowledge'                  => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/special-notice-acknowledge/:id',
                    'defaults' => [
                        'controller' => MotTest\SpecialNoticesController::class,
                        'action'     => 'acknowledgeSpecialNotice',
                    ],
                ],
            ],
            'vehicle-step'                                => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle-step[/:action]',
                    'defaults' => [
                        'controller' => CreateVehicleController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'vehicle-makes'                               => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle-makes',
                    'defaults' => [
                        'controller' => MotTest\VehicleDictionaryController::class,
                        'action'     => 'findMake',
                    ],
                ],
            ],
            'vehicle-models'                              => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle-models',
                    'defaults' => [
                        'controller' => MotTest\VehicleDictionaryController::class,
                        'action'     => 'findModel',
                    ],
                ],
            ],
            'vehicle-search'                              => [
                'type'          => 'segment',
                'options'       => [
                    'route'    => '/vehicle-search',
                    'defaults' => [
                        'controller' => MotTest\VehicleSearchController::class,
                        'action'     => 'vehicleSearch',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'not-found'  => [
                        'type'    => 'segment',
                        'options' => [
                            'route' => '/not-found'
                        ],
                    ],
                    'create-new' => [
                        'type'    => 'segment',
                        'options' => [
                            'route' => '/create-new'
                        ],
                    ],
                ],
            ],
            'tester-mot-test-log' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/mot-test-log',
                    'defaults' => [
                        'controller' => MotTest\TesterMotTestLogController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'download'    => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/csv',
                            'defaults'    => [
                                'controller' => MotTest\TesterMotTestLogController::class,
                                'action'     => 'downloadCsv',
                            ],
                        ],
                    ],
                ],
            ],
            'replacement-certificate-vehicle-search'      => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/replacement-certificate-vehicle-search',
                    'defaults' => [
                        'controller' => MotTest\VehicleSearchController::class,
                        'action'     => 'replacementCertificateVehicleSearch',
                    ],
                ],
            ],
            'vehicle-test-history'                        => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle/:id/test-history',
                    'defaults' => [
                        'controller' => MotTest\VehicleSearchController::class,
                        'action'     => 'testHistory',
                    ],
                ],
            ],
            // TODO remove
            'vehicle-class-rfr-groups'                    => [
                'type'          => 'Segment',
                'options'       => [
                    'route'       => '/vehicle-class/:id/rfr-groups',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => MotTest\TestItemSelectorController::class,
                        'action'     => 'suggestions',
                    ],
                ],
                'may_terminate' => true,
            ],
            'dvsa-vehicle-test-history'                   => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/vehicle/:id/dvsa-test-history',
                    'defaults' => [
                        'controller' => MotTest\VehicleSearchController::class,
                        'action'     => 'dvsaTestHistory',
                    ],
                ],
            ],
            'training-test-vehicle-search'                         => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/training-test-vehicle-search',
                    'defaults' => [
                        'controller' => MotTest\VehicleSearchController::class,
                        'action'     => 'trainingTestVehicleSearch',
                    ],
                ],
            ],
            'non-mot-test-vehicle-search'                         => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/non-mot-test-vehicle-search',
                    'defaults' => [
                        'controller' => MotTest\VehicleSearchController::class,
                        'action'     => 'nonMotVehicleSearch',
                    ],
                ],
            ],
            'start-test-confirmation'                     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/start-test-confirmation[/:id[/:noRegistration[/:source]]]',
                    'constraints' => [
                        'id'             => '[0-9a-zA-Z-_]+',   // vehicleId are obfuscated
                        'noRegistration' => '[0-9]+',
                        'source'         => '[0-9]+'
                    ],
                    'defaults'    => [
                        'controller' => MotTest\StartTestConfirmationController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'start-training-test-confirmation'                     => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/start-training-test-confirmation[/:id[/:noRegistration[/:source]]]',
                    'constraints' => [
                        'id'             => '[0-9a-zA-Z-_]+',   // vehicleId are obfuscated
                        'noRegistration' => '[0-9]+',
                        'source'         => '[0-9]+'
                    ],
                    'defaults'    => [
                        'controller' => MotTest\StartTestConfirmationController::class,
                        'action'     => 'training',
                    ],
                ],
            ],
            NonMotTestUrlTemplate::START_NON_MOT_TEST_CONFIRMATION_ROUTE    => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/start-non-mot-test-confirmation[/:id[/:noRegistration[/:source]]]',
                    'constraints' => [
                        'id'             => '[0-9a-zA-Z-_]+',   // vehicleId are obfuscated
                        'noRegistration' => '[0-9]+',
                        'source'         => '[0-9]+'
                    ],
                    'defaults'    => [
                        'controller' => MotTest\StartTestConfirmationController::class,
                        'action'     => 'nonMotTest',
                    ],
                ],
            ],
            'refuse-to-test'                              => [
                'type'          => 'segment',
                'options'       => [
                    'route'       => '/refuse-to-test/:testTypeCode[/:id]',
                    'constraints' => [
                        'id'           => '[0-9a-zA-Z-_]+',   // vehicleId are obfuscated
                        'testTypeCode' => '[A-Z]{2}',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'reason'  => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/reason',
                            'defaults' => [
                                'controller' => MotTest\RefuseToTestController::class,
                                'action'     => 'refuseToTestReason',
                            ],
                        ],
                    ],
                    'summary' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/summary',
                            'defaults' => [
                                'controller' => MotTest\RefuseToTestController::class,
                                'action'     => 'refuseToTestSummary',
                            ],
                        ],
                    ],
                    'print'   => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/print',
                            'defaults' => [
                                'controller' => MotTest\RefuseToTestController::class,
                                'action'     => 'refuseToTestPrint',
                            ],
                        ],
                    ],
                ],
            ],
            'mot-test'                                    => [
                'type'          => 'segment',
                'options'       => [
                    'route'       => '/mot-test/:motTestNumber',
                    'constraints' => [
                        'motTestNumber' => MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => MotTestController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'options'                     => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/options',
                            'defaults' => [
                                'controller' => MotTest\MotTestOptionsController::class,
                                'action'     => 'motTestOptions',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'mot-checklist' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/checklist',
                                    'defaults' => [
                                        'controller' => MotTest\MotTestOptionsController::class,
                                        'action'     => 'motChecklist',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'odometer-update'             => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/odometer-update',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'updateOdometer',
                            ],
                        ],
                    ],
                    'brake-test-configuration'    => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/brake-test-configuration',
                            'defaults' => [
                                'controller' => MotTest\BrakeTestResultsController::class,
                                'action'     => 'configureBrakeTest',
                            ],
                        ],
                    ],
                    'brake-test-results'          => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/brake-test-results',
                            'defaults' => [
                                'controller' => MotTest\BrakeTestResultsController::class,
                                'action'     => 'addBrakeTestResults',
                            ],
                        ],
                    ],
                    'brake-test-summary'          => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/brake-test-summary',
                            'defaults' => [
                                'controller' => MotTest\BrakeTestResultsController::class,
                                'action'     => 'displayBrakeTestSummary',
                            ],
                        ],
                    ],
                    'submit-test-results'         => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/submit-test-results',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'submitTestResults',
                            ],
                        ],
                    ],
                    'test-summary'                => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/test-summary',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'displayTestSummary',
                            ],
                        ],
                    ],
                    'cancel'                      => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/cancel',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'cancelMotTest',
                            ],
                        ],
                    ],
                    'cancelled'                   => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/cancelled',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'cancelledMotTest',
                            ],
                        ],
                    ],
                    'short-summary'               => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/short-summary',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'shortSummary',
                            ],
                        ],
                    ],
                    'reason-for-aborting'         => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/reason-for-aborting',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'reasonForAbortingMotTest',
                            ],
                        ],
                    ],
                    'abort-success'               => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/abort-success',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'abortedMotTest',
                                'success'    => true,
                            ],
                        ],
                    ],
                    'abort-fail'                  => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/abort-fail',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'abortedMotTest',
                                'success'    => false,
                            ],
                        ],
                    ],
                    'test-result'           => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/test-result',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'testResult',
                            ],
                        ],
                    ],
                    'print-duplicate-test-result' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/print-duplicate-test-result',
                            'defaults' => [
                                'controller' => MotTestController::class,
                                'action'     => 'printDuplicateCertificateResult',
                            ],
                        ],
                    ],
                    'print-certificate'           => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/print-certificate',
                            'defaults' => [
                                'controller' => MotTest\CertificatePrintingController::class,
                                'action'     => 'retrievePdf',
                            ],
                        ],
                    ],
                    'print-duplicate-certificate' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/print-duplicate-certificate',
                            'defaults' => [
                                'controller'  => MotTest\CertificatePrintingController::class,
                                'action'      => 'retrievePdf',
                                'isDuplicate' => true,
                            ],
                        ],
                    ],
                    'reason-for-rejection'        => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/reason-for-rejection/:rfr-id',
                            'constraints' => [
                                'rfr-id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => MotTestController::class,
                                'action'     => 'deleteReasonForRejection',
                            ],
                        ],
                    ],
                    'test-item-selector'          => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/test-item-selector[/:tis-id]',
                            'constraints' => [
                                'tis-id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => MotTest\TestItemSelectorController::class,
                                'action'     => 'testItemSelectors',
                            ],
                        ],
                    ],
                    'test-item-selector-search'   => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/test-item-selector-search',
                            'defaults' => [
                                'controller' => MotTest\TestItemSelectorController::class,
                                'action'     => 'search',
                            ],
                        ],
                    ],
                    'rfr-add'                     => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/reason-for-rejection/:tis-id/rfr-add/:rfr-id',
                            'constraints' => [
                                'tis-id' => '[0-9]+',
                                'rfr-id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => MotTest\TestItemSelectorController::class,
                                'action'     => 'addReasonForRejection',
                            ],
                        ],
                    ],
                    'rfr-edit'                    => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/rfr-edit/:rfr-id',
                            'constraints' => [
                                'rfr-id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => MotTest\TestItemSelectorController::class,
                                'action'     => 'editReasonForRejection',
                            ],
                        ],
                    ],
                    'replacement-certificate'                     => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/replacement-certificate[/:id]',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => MotTest\ReplacementCertificateController::class,
                                'action'     => 'replacementCertificate',
                            ],
                        ],
                        'may_terminate' => 'true',
                        'child_routes'  => [
                            'select-model' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/select-model[/:makeCode]',
                                    'constraints' => [
                                        'makeCode' => '[0-9a-zA-Z-_]+',
                                    ]
                                ]
                            ],
                            'other-vehicle' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/other-vehicle[/:makeCode]',
                                    'constraints' => [
                                        'makeCode' => '[0-9a-zA-Z-_]+',
                                    ],
                                    'defaults' => [
                                        'controller' => MotTest\ReplacementCertificateController::class,
                                        'action'     => 'otherVehicle',
                                    ],
                                ],
                            ],
                            'summary' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/summary',
                                    'defaults' => [
                                        'controller' => MotTest\ReplacementCertificateController::class,
                                        'action'     => 'review',
                                    ],
                                ],
                            ],
                            'finish'  => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/finish',
                                    'defaults' => [
                                        'controller' => MotTest\ReplacementCertificateController::class,
                                        'action'     => 'finish',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'mot-test-certificate'                        => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/mot-test-certificate/:motTestNumber',
                    'constraints' => [
                        'motTestNumber' => \DvsaCommon\Constants\MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => MotTestController::class,
                        'action'     => 'displayCertificateSummary',
                    ],
                ],
            ],
            'location-select'                             => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/location-select',
                    'defaults' => [
                        'controller' => MotTest\LocationSelectController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'mot-test-search'                             => [
                'type'          => 'segment',
                'options'       => [
                    'route'    => '/mot-test-search',
                    'defaults' => [
                        'controller' => EnforcementMotTestSearchController::class,
                        'action'     => 'motTestSearch',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'vts'     => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => '/vts',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByVts',
                            ],
                        ],
                    ],
                    'vtsDate' => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => '/vtsDate',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByDateRange',
                            ],
                        ],
                    ],
                    'tester'  => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => '/tester',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByDateRange',
                            ],
                        ],
                    ],
                    'vrm'     => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => '/vrm',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByVrmOrVin',
                            ],
                        ],
                    ],
                    'testNumber'     => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => '/testNumber',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByMotTestNumber',
                            ],
                        ],
                    ],
                    'vin'     => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => '/vin',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByVrmOrVin',
                            ],
                        ],
                    ],
                ],
            ],
            'enforcement-list-recent-mot-tests-api'       => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/api/enforcement/mot-test/:siteNumber',
                    'defaults' => [
                        'controller' => Ajax\MotTestApiController::class,
                        'action'     => 'examinerFetchRecentMotTestData',
                    ],
                ],
            ],
            'enforcement-list-mot-tests-by-date-api'      => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/api/enforcement/mot-tests-by-date-api/:search',
                    'defaults' => [
                        'controller' => Ajax\MotTestApiController::class,
                        'action'     => 'examinerFetchMotTestByDate',
                    ],
                ],
            ],
            'enforcement-view-mot-test'                   => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/enforcement/mot-test/:motTestNumber/test-summary',
                    'constraints' => [
                        'motTestNumber' => \DvsaCommon\Constants\MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => EnforcementMotTestController::class,
                        'action'     => 'displayTestSummary',
                    ],
                ],
            ],
            'enforcement-start-inspection'                => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/enforcement/mot-test/:motTestNumber/start-inspection',
                    'constraints' => [
                        'motTestNumber' => \DvsaCommon\Constants\MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => EnforcementMotTestController::class,
                        'action'     => 'startInspection',
                    ],
                ],
            ],
            'enforcement-abort-mot-test'                  => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/enforcement/mot-test/:motTestNumber/abort',
                    'constraints' => [
                        'motTestNumber' => \DvsaCommon\Constants\MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => EnforcementMotTestController::class,
                        'action'     => 'abortMotTest',
                    ],
                ],
            ],
            'enforcement-differences-found-between-tests' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/enforcement/mot-test/:motTestNumber/differences-found-between-tests',
                    'constraints' => [
                        'motTestNumber' => \DvsaCommon\Constants\MotTestNumberConstraint::FORMAT_REGEX,
                    ],
                    'defaults'    => [
                        'controller' => EnforcementMotTestController::class,
                        'action'     => 'differencesFoundBetweenTests',
                    ],
                ],
            ],
            'enforcement-compare-tests'                   => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/enforcement/mot-test/compare[/:motTestNumber][/:motTestNumberToCompare]',
                    'defaults' => [
                        'controller' => EnforcementMotTestController::class,
                        'action'     => 'motTestStartCompare',
                    ],
                ],
            ],
            'enforcement-record-assessment-confirmation'                   => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/enforcement/mot-test/:motTestNumber/record-assessment-confirmation/:resultId',
                    'defaults' => [
                        'controller' => Enforcement\ReinspectionReportController::class,
                        'action'     => 'recordAssessmentConfirmation',
                    ],
                ],
            ],
            'report'                                      => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/report/choose',
                    'defaults' => [
                        'controller' => Application\ReportController::class,
                        'action'     => 'choose',
                    ],
                ],
            ],
        ],
    ],
    'service_manager'            => [
        'abstract_factories' => [
            AutoWireFactory::class,
            \Zend\Cache\Service\StorageCacheAbstractServiceFactory::class,
            \Zend\Log\LoggerAbstractServiceFactory::class,
        ],
        'aliases'            => [
            'translator' => 'MvcTranslator',
        ],
        'factories' => [
            MotChecklistPdfService::class => MotChecklistPdfServiceFactory::class,
            CanTestWithoutOtpService::class    => CanTestWithoutOtpServiceFactory::class,

        ],
    ],
    'session_namespace_prefixes' => [
        'DvsaMotEnforcement\\Session\\',
    ],

    'controller_plugins'         => [
        'invokables' => [
            'ajaxResponse' => AjaxResponsePlugin::class,
        ],
    ],
    'view_manager'               => [
        'display_not_found_reason' => true,
        'display_exceptions'       => (getenv('APPLICATION_ENV') === 'development'),
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => [
            'address'                                                                    =>
                __DIR__ . '/../view/partials/address.phtml',
            'layout/layout_enforcement'                                                  =>
                __DIR__ . '/../view/layout/layout_enforcement.phtml',
            'error/404'                                                                  =>
                __DIR__ . '/../view/error/404.phtml',
            'error/index'                                                                =>
                __DIR__ . '/../view/error/index.phtml',
            'error/flash-error'                                                          =>
                __DIR__ . '/../view/error/flash-error.phtml',
            'signOut'                                                                    =>
                __DIR__ . '/../view/partials/sign-out.phtml',
            'vehicleExaminerDetails'                                                     =>
                __DIR__ . '/../view/partials/vehicleExaminerDetails.phtml',
            'slots'                                                                      =>
                __DIR__ . '/../view/partials/slots.phtml',
            'errorMessages'                                                              =>
                __DIR__ . '/../view/partials/errorMessages.phtml',
            'infoMessages'                                                               =>
                __DIR__ . '/../view/partials/infoMessages.phtml',
            'testItemSelectorSearch'                                                     =>
                __DIR__ . '/../view/partials/testItemSelectorSearch.phtml',
            'motHeaderDetails'                                                           =>
                __DIR__ . '/../view/partials/motHeaderDetails.phtml',
            'rfrList'                                                                    =>
                __DIR__ . '/../view/partials/rfrList.phtml',
            'rfrBreadcrumb'                                                              =>
                __DIR__ . '/../view/partials/rfrBreadcrumb.phtml',
            'rfrLocationModal'                                                           =>
                __DIR__ . '/../view/partials/rfrLocationModal.phtml',
            'rfrEditLocationModal'                                                       =>
                __DIR__ . '/../view/partials/rfrEditLocationModal.phtml',
            'rfrResults'                                                                 =>
                __DIR__ . '/../view/partials/rfr-results.phtml',
            'rfrResultsSummary'                                                          =>
                __DIR__ . '/../view/partials/rfr-results-summary.phtml',
            'rfrResultSet'                                                               =>
                __DIR__ . '/../view/partials/rfr-result-set.phtml',
            'brakeTestResult'                                                            =>
                __DIR__ . '/../view/partials/brakeTestResult.phtml',
            'brakeTestInput'                                                             =>
                __DIR__ . '/../view/partials/brakeTestInput.phtml',
            'motTestProgress'                                                            =>
                __DIR__ . '/../view/partials/motTestProgress.phtml',
            'motTestPopover'                                                             =>
                __DIR__ . '/../view/partials/motTestPopover.phtml',
            'motTestSearchSummaryLink'                                                   =>
                __DIR__ . '/../view/partials/enforcement/motTestSearchSummaryLink.phtml',
            'vehicleSummary'                                                             =>
                __DIR__ . '/../view/partials/vehicleSummary.phtml',
            'dldtdd'                                                                     =>
                __DIR__ . '/../view/partials/dldtdd.phtml',
            'modalMessages'                                                              =>
                __DIR__ . '/../view/partials/modalMessages.phtml',
            'carColours'                                                                 =>
                __DIR__ . '/../view/partials/carColours.phtml',
            'confirmationVehicleSummary'                                                 =>
                __DIR__ . '/../view/partials/confirmationVehicleSummary.phtml',
            'vehicleSearch'                                                              =>
                __DIR__ . '/../view/partials/vehicleSearch.phtml',
            'vehicleRetestSearch'                                                        =>
                __DIR__ . '/../view/partials/vehicleRetestSearch.phtml',
            'contingencyMotTest'                                                         =>
                __DIR__ . '/../view/partials/contingencyMotTest.phtml',
            'specialNotice'                                                              =>
                __DIR__ . '/../view/partials/specialNotice.phtml',
            'motTestSearch'                                                              =>
                __DIR__ . '/../view/partials/motTestSearch.phtml',
            'genSearchVTS'                                                               =>
                __DIR__ . '/../view/partials/genericSearchResultsVTS.phtml',
            'otpInput'                                                                   =>
                __DIR__ . '/../view/partials/otpInput.phtml',
            '2faDeclaration'                                                             =>
                __DIR__ . '/../view/partials/2faDeclaration.phtml',
            'otpError'                                                                   =>
                __DIR__ . '/../view/partials/otpError.phtml',
            'checkboxesElement'                                                          =>
                __DIR__ . '/../view/partials/checkboxesElement.phtml',
            'searchAgain'                                                                =>
                __DIR__ . '/../view/partials/searchAgain.phtml',
            'primaryAction/tester'                                                       =>
                __DIR__ . '/../view/partials/primary-action/tester.phtml',
            'primaryAction/finance'                                                       =>
                __DIR__ . '/../view/partials/primary-action/finance.phtml',
            'primaryAction/testerApplicant'                                              =>
                __DIR__ . '/../view/partials/primary-action/tester-applicant.phtml',
            'primaryAction/user'                                                         =>
                __DIR__ . '/../view/partials/primary-action/user.phtml',
            'primaryAction/aedm'                                                         =>
                __DIR__ . '/../view/partials/primary-action/aedm.phtml',
            'primaryAction/admin'                                                        =>
                __DIR__ . '/../view/partials/primary-action/admin.phtml',
            'primaryAction/vehicle-examiner'                                             =>
                __DIR__ . '/../view/partials/primary-action/vehicle-examiner.phtml',
            'primaryAction/links'                                                        =>
                __DIR__ . '/../view/partials/primary-action/links.phtml',
            'dashboard/specialNotice'                                                    =>
                __DIR__ . '/../view/partials/dashboard/special-notice.phtml',
            'dashboard/veList'                                                           =>
                __DIR__ . '/../view/partials/dashboard/ve-list.phtml',
            'dashboard/vtsList'                                                          =>
                __DIR__ . '/../view/partials/dashboard/vts-list.phtml',
            'dashboard/aeHeader'                                                         =>
                __DIR__ . '/../view/partials/dashboard/authorised-examiner-header.phtml',
            'dashboard/dvsaAdminBox'                                                     =>
                __DIR__ . '/../view/partials/dashboard/dvsa-admin-box.phtml',
            'dashboard/testerStatsBox'                                                   =>
                __DIR__ . '/../view/partials/dashboard/tester-stats-box.phtml',
            'dashboard/testerContingencyBox'                                             =>
                __DIR__ . '/../view/partials/dashboard/tester-contingency-box.phtml',
            'dashboard/financeBox'                                                       =>
                __DIR__ . '/../view/partials/dashboard/finance-box.phtml',
            'dashboard/trainingTestBox'                                                  =>
                __DIR__ . '/../view/partials/dashboard/tester-training-test-box.phtml',
            'dashboard/nonMotTestBox'                                                    =>
                __DIR__ . '/../view/partials/dashboard/non-mot-test-box.phtml',
            'dashboard/motActivity'                                                      =>
                __DIR__ . '/../view/partials/dashboard/mot-activity.phtml',
            'vehicle/history'                                                            =>
                __DIR__ . '/../view/partials/vehicle-history/history.phtml',
            'vehicle/history-item'                                                       => __DIR__ .
                '/../view/partials/vehicle-history/history-item.phtml',
            'brake-test-results-class-1-and-2/brake-effort'                              => __DIR__ .
                '/../view/partials/brake-test-results-class-1-and-2/brake-effort.phtml',
            'brake-test-results-class-1-and-2/vehicle-weight-details'                    => __DIR__ .
                '/../view/partials/brake-test-results-class-1-and-2/vehicle-weight-details.phtml',
            'brake-test-results-class-3-and-above/parking-brake-effort-table'            => __DIR__ .
                '/../view/partials/brake-test-results-class-3-and-above/parking-brake-effort-table.phtml',
            'brake-test-results-class-3-and-above/service-brake-effort-table'            => __DIR__ .
                '/../view/partials/brake-test-results-class-3-and-above/service-brake-effort-table.phtml',
            'brake-test-results-class-3-and-above/service-brake-effort-row-single-input' => __DIR__ .
                '/../view/partials/brake-test-results-class-3-and-above/service-brake-effort-row-single-input.phtml',
            'brake-test-results-class-3-and-above/service-brake-effort-row-double-input' => __DIR__ .
                '/../view/partials/brake-test-results-class-3-and-above/service-brake-effort-row-double-input.phtml',
            'brake-test-results-class-3-and-above/parking-brake-effort-row-double-input' => __DIR__ .
                '/../view/partials/brake-test-results-class-3-and-above/parking-brake-effort-row-double-input.phtml',
            'brake-test-results-class-3-and-above/brake-efficiency-box'                  => __DIR__ .
                '/../view/partials/brake-test-results-class-3-and-above/brake-efficiency-box.phtml',
        ],
        'template_path_stack'      => [
            __DIR__ . '/../view',
        ],
        'strategies'               => [
            'ViewJsonStrategy',
        ],
    ],
    // Placeholder for console routes
    'console'                    => [
        'router' => [
            'routes' => [],
        ],
    ],
    'view_helpers'               => [
        'invokables' => [
            'camelCaseToReadable'               => CamelCaseToReadable::class,
            'camelCaseToFirstUppercaseReadable' => CamelCaseToFirstUppercaseReadable::class,
        ],
        'factories'  => [
            'identityHelper'         => IdentityHelperFactory::class,
            'authorisationHelper'    => AuthorisationHelperFactory::class,
            'dashboardDataProvider'  => DashboardDataProviderFactory::class,
            'currentMotTest'         => CurrentMotTestFactory::class,
            'getSites'               => GetSitesFactory::class,
            'locationSelector'       => LocationSelectorFactory::class,
            'getSiteCount'           => GetSiteCountFactory::class,
            'manualsHelper'          => ManualsAndGuidesFactory::class,
            'resourcesOnGovUkHelper' => ResourcesOnGovUkFactory::class,
            'canTestWithoutOtp'      => CanTestWithoutOtpFactory::class,
        ]
    ],
    'module_layouts'             => [
        'Application'           => 'application/layout',
        'Dvsa\Mot\Frontend\AuthenticationModule'    => 'application/layout',
        'DvsaMotEnforcement'    => 'application/layout',
        'DvsaMotEnforcementApi' => 'application/layout',
        'DvsaMotTest'           => 'application/layout',
    ],
    'validators' => [
        'invokables' => [
            'SpecialNoticePublishDate' => SpecialNoticePublishDateValidator::class
        ]
    ],
    'breadcrumbs' => [
        'resolvers' => [
            'site' => SiteNameResolver::class,
            'organisationBySite' => OrganisationNameBySiteResolver::class,
            'simple' => SimpleResolver::class
        ]
    ]
];
