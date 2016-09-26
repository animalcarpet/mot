<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

use Dvsa\Mot\Frontend\PersonModule\Controller\AddAnnualAssessmentCertificatesController;
use Dvsa\Mot\Frontend\PersonModule\Controller\TestQualityInformationController;
use Dvsa\Mot\Frontend\PersonModule\Controller\RemoveAnnualAssessmentCertificatesController;
use Dvsa\Mot\Frontend\PersonModule\Controller\ViewAnnualAssessmentCertificatesController;
use Dvsa\Mot\Frontend\PersonModule\Controller\EditAnnualAssessmentCertificatesController;
use Dvsa\Mot\Frontend\PersonModule\Controller\QualificationDetailsController;
use Dashboard\Factory\Controller\SecurityQuestionControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeNameController;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeAddressController;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeTelephoneController;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaMotTest\Controller\TesterMotTestLogController;
use UserAdmin\Controller\EmailAddressController;
use Site\Controller\SiteController;
use UserAdmin\Factory\Controller\DrivingLicenceControllerFactory;
use Dashboard\Controller\UserTradeRolesController;
use Dashboard\Factory\Controller\PasswordControllerFactory;
use UserAdmin\Factory\Controller\UserProfileControllerFactory;
use UserAdmin\Controller\ChangeQualificationStatusController;
use UserAdmin\Factory\Controller\ResetAccountClaimByPostControllerFactory;
use UserAdmin\Factory\Controller\PersonRoleControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeDateOfBirthController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionOneController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionTwoController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsReviewController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsConfirmationController;

return [
    'router' => [
        'routes' => [
            ContextProvider::YOUR_PROFILE_PARENT_ROUTE => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/your-profile',
                    'defaults' => [
                        'controller' => PersonProfileController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'change-security-questions' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/change-security-questions',
                            'defaults' => [
                                'controller' => ChangeSecurityQuestionsController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'question-one' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/question-one',
                                    'defaults' => [
                                        'controller' => ChangeSecurityQuestionOneController::class,
                                    ],
                                ],
                            ],
                            'question-two' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/question-two',
                                    'defaults' => [
                                        'controller' => ChangeSecurityQuestionTwoController::class,
                                    ],
                                ],
                            ],
                            'review' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/review',
                                    'defaults' => [
                                        'controller' => ChangeSecurityQuestionsReviewController::class,
                                    ],
                                ],
                            ],
                            'confirmation' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'controller' => ChangeSecurityQuestionsConfirmationController::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'address' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/address',
                            'defaults' => [
                                'controller' => ChangeAddressController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'change-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/change',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'review-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/review',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'review',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'newProfileEmail' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/email/change',
                            'defaults' => [
                                'controller' => EmailAddressController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may-terminate' => true,
                    ],
                    'driving-licence-change' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/driving-licence',
                            'defaults' => [
                                'controller' => DrivingLicenceControllerFactory::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'summary' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/summary',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'summary'
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'delete'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-telephone-number' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/telephone/change',
                            'defaults' => [
                                'controller' => ChangeTelephoneController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'trade-roles'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/trade-roles',
                            'defaults'    => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'annual-assessment-certificates' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/annual-assessment-certificates',
                            'defaults'    => [
                                'controller' => ViewAnnualAssessmentCertificatesController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults'    => [
                                        'controller' => AddAnnualAssessmentCertificatesController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => AddAnnualAssessmentCertificatesController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/:certificateId/edit',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+'
                                    ],
                                    'defaults'    => [
                                        'controller' => EditAnnualAssessmentCertificatesController::class,
                                        'action'     => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => EditAnnualAssessmentCertificatesController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/remove/:certificateId',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+',
                                    ],
                                    'defaults'    => [
                                        'controller' => RemoveAnnualAssessmentCertificatesController::class,
                                        'action'     => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'qualification-details' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/qualification-details',
                            'defaults'    => [
                                'controller' => QualificationDetailsController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults'    => [
                                        'controller' => QualificationDetailsController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                    'add-confirmation' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/confirmation',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addConfirmation',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/edit',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/remove',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'remove-ae-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-ae-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeAeRole'
                            ]
                        ]
                    ],
                    'remove-vts-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-vts-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeVtsRole'
                            ]
                        ]
                    ],
                    'test-quality-information' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/test-quality-information/:month/:year',
                            'constraints' => [
                                'month' => '[0-9]+',
                                'year' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => TestQualityInformationController::class,
                                'action' => 'testQualityInformation',
                            ],
                        ],
                        'child_routes' => [
                            'component-breakdown-at-site' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/components-at-site/:site/:group',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                        'group' => 'A|B',
                                        'site' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'controller' => SiteController::class,
                                        'action' => 'userTestQuality',
                                    ],
                                ],
                            ],
                            'component-breakdown' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/components/:group',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults' => [
                                        'controller' => TestQualityInformationController::class,
                                        'action' => 'componentBreakdown',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-password'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/change-password',
                            'defaults' => [
                                'controller' => PasswordControllerFactory::class,
                                'action' => 'changePassword'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'confirmation' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'action' => 'confirmation',
                                    ],
                                ],
                            ],
                        ],

                    ],
                    'security-settings' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/security-settings',
                            'defaults' => [
                                'controller' => PersonProfileController::class,
                                'action' => 'securitySettings',
                            ],
                        ],
                    ],
                    'security-questions' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/security-question[/:questionNumber]',
                            'constraints' => [
                                'questionNumber' => '1|2',
                            ],
                            'defaults' => [
                                'controller' => SecurityQuestionControllerFactory::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'username-recover' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/username-recover',
                            'defaults' => [
                                'controller' => UserProfileControllerFactory::class,
                                'action' => 'usernameRecover'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ok' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ok',
                                    'defaults' => [
                                        'controller' => UserProfileControllerFactory::class,
                                        'action' => 'usernameRecoverOk'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-qualification-status' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change-qualification-status/:vehicleClassGroup',
                            'defaults' => [
                                'controller' => ChangeQualificationStatusController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'confirmation' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/confirmation',
                                    'defaults' => [
                                        'controller' => ChangeQualificationStatusController::class,
                                        'action' => 'confirmation',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'tester-mot-test-log' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/mot-test-log',
                            'defaults' => [
                                'controller' => TesterMotTestLogController::class,
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
                                        'controller' => TesterMotTestLogController::class,
                                        'action'     => 'downloadCsv',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            ContextProvider::USER_SEARCH_PARENT_ROUTE => [
                'type' => 'segment',
                'options' => [
                    'route' => '/user-admin/user/[:id]',
                    'defaults' => [
                        'controller' => PersonProfileController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'address' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/address',
                            'defaults' => [
                                'controller' => ChangeAddressController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'change-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/change',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'review-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/review',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'review',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'userAdminEmail' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/email/change',
                            'defaults' => [
                                'controller' => EmailAddressController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may-terminate' => true,
                    ],
                    'driving-licence-change' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/driving-licence',
                            'defaults' => [
                                'controller' => DrivingLicenceControllerFactory::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'summary' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/summary',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'summary'
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'delete'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'trade-roles'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/trade-roles',
                            'defaults'    => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'annual-assessment-certificates' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/annual-assessment-certificates',
                            'defaults'    => [
                                'controller' => ViewAnnualAssessmentCertificatesController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults'    => [
                                        'controller' => AddAnnualAssessmentCertificatesController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => AddAnnualAssessmentCertificatesController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/:certificateId/edit',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+'
                                    ],
                                    'defaults'    => [
                                        'controller' => EditAnnualAssessmentCertificatesController::class,
                                        'action'     => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => EditAnnualAssessmentCertificatesController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/remove/:certificateId',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+',
                                    ],
                                    'defaults'    => [
                                        'controller' => RemoveAnnualAssessmentCertificatesController::class,
                                        'action'     => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'qualification-details' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/qualification-details',
                            'defaults'    => [
                                'controller' => QualificationDetailsController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults'    => [
                                        'controller' => QualificationDetailsController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                    'add-confirmation' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/confirmation',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addConfirmation',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/edit',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/remove',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'remove-ae-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-ae-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeAeRole'
                            ]
                        ]
                    ],
                    'remove-vts-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-vts-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeVtsRole'
                            ]
                        ]
                    ],
                    'manage-user-internal-role' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/manage-internal-role',
                            'defaults' => [
                                'controller' => PersonRoleControllerFactory::class,
                                'action' => 'manageInternalRole',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add-internal-role' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/add/:personSystemRoleId',
                                    'constraints' => [
                                        'personSystemRoleId' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'controller' => PersonRoleControllerFactory::class,
                                        'action' => 'addInternalRole',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'remove-internal-role' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/remove/:personSystemRoleId',
                                    'constraints' => [
                                        'personSystemRoleId' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'controller' => PersonRoleControllerFactory::class,
                                        'action' => 'removeInternalRole',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ],
                    'test-quality-information' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/test-quality-information/:month/:year',
                            'constraints' => [
                                'month' => '[0-9]+',
                                'year' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => TestQualityInformationController::class,
                                'action' => 'testQualityInformation',
                            ],
                        ],
                        'child_routes' => [
                            'component-breakdown-at-site' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/components-at-site/:site/:group',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                        'group' => 'A|B',
                                        'site' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'controller' => SiteController::class,
                                        'action' => 'userTestQuality',
                                    ],
                                ],
                            ],
                            'component-breakdown' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/components/:group',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults' => [
                                        'controller' => TestQualityInformationController::class,
                                        'action' => 'componentBreakdown',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-password'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/change-password',
                            'defaults' => [
                                'controller' => PasswordControllerFactory::class,
                                'action' => 'changePassword'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'confirmation' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'action' => 'confirmation',
                                    ],
                                ],
                            ],
                        ],

                    ],
                    'username-recover' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/username-recover',
                            'defaults' => [
                                'controller' => UserProfileControllerFactory::class,
                                'action' => 'usernameRecover'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ok' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ok',
                                    'defaults' => [
                                        'controller' => UserProfileControllerFactory::class,
                                        'action' => 'usernameRecoverOk'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-qualification-status' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change-qualification-status/:vehicleClassGroup',
                            'defaults' => [
                                'controller' => ChangeQualificationStatusController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'confirmation' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/confirmation',
                                    'defaults' => [
                                        'controller' => ChangeQualificationStatusController::class,
                                        'action' => 'confirmation',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'claim-reset' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/claim-reset',
                            'defaults' => [
                                'controller' => UserProfileControllerFactory::class,
                                'action' => 'claimAccount',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'claim-reset-by-post' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/claim-reset/post',
                            'defaults' => [
                                'controller' => ResetAccountClaimByPostControllerFactory::class,
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'user-security-question' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/security-question/:questionNumber',
                            'constraints' => [
                                'questionNumber' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => SecurityQuestionControllerFactory::class,
                            ],
                        ],
                    ],
                    'password-reset' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/password-reset',
                            'defaults' => [
                                'controller' => UserProfileControllerFactory::class,
                                'action' => 'passwordReset'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ok' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ok',
                                    'defaults' => [
                                        'controller' => UserProfileControllerFactory::class,
                                        'action' => 'passwordResetOk'
                                    ],
                                ],
                            ],
                            'nok' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/nok',
                                    'defaults' => [
                                        'controller' => UserProfileControllerFactory::class,
                                        'action' => 'passwordResetNok'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-person-name' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/name/change',
                            'defaults' => [
                                'controller' => ChangeNameController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'change-person-date-of-birth' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/date-of-birth',
                            'defaults' => [
                                'controller' => ChangeDateOfBirthController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'change-telephone-number' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/telephone/change',
                            'defaults' => [
                                'controller' => ChangeTelephoneController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            ContextProvider::VTS_PARENT_ROUTE => [
                'type' => 'segment',
                'options' => [
                    'route' => '/vehicle-testing-station/[:vehicleTestingStationId]/user/[:id]',
                    'defaults' => [
                        'controller' => PersonProfileController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'address' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/address',
                            'defaults' => [
                                'controller' => ChangeAddressController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'change-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/change',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'review-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/review',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'review',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'VTSEmail' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/email/change',
                            'defaults' => [
                                'controller' => EmailAddressController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may-terminate' => true,
                    ],
                    'driving-licence-change' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/driving-licence',
                            'defaults' => [
                                'controller' => DrivingLicenceControllerFactory::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'summary' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/summary',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'summary'
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'delete'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'trade-roles'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/trade-roles',
                            'defaults'    => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'annual-assessment-certificates' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/annual-assessment-certificates',
                            'defaults'    => [
                                'controller' => ViewAnnualAssessmentCertificatesController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults'    => [
                                        'controller' => AddAnnualAssessmentCertificatesController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => AddAnnualAssessmentCertificatesController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/:certificateId/edit',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+',
                                    ],
                                    'defaults'    => [
                                        'controller' => EditAnnualAssessmentCertificatesController::class,
                                        'action'     => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => EditAnnualAssessmentCertificatesController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/remove/:certificateId',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+',
                                    ],
                                    'defaults'    => [
                                        'controller' => RemoveAnnualAssessmentCertificatesController::class,
                                        'action'     => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'qualification-details' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/qualification-details',
                            'defaults'    => [
                                'controller' => QualificationDetailsController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults'    => [
                                        'controller' => QualificationDetailsController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                    'add-confirmation' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/confirmation',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addConfirmation',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/edit',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/remove',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults'    => [
                                        'controller' => QualificationDetailsController::class,
                                        'action'     => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'remove-ae-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-ae-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeAeRole'
                            ]
                        ]
                    ],
                    'remove-vts-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-vts-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeVtsRole'
                            ]
                        ]
                    ],
                    'test-quality-information' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/test-quality-information/:month/:year',
                            'constraints' => [
                                'month' => '[0-9]+',
                                'year' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => TestQualityInformationController::class,
                                'action' => 'testQualityInformation',
                            ],
                        ],
                        'child_routes' => [
                            'component-breakdown' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/components/:group',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults' => [
                                        'controller' => TestQualityInformationController::class,
                                        'action' => 'componentBreakdown',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-password'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/change-password',
                            'defaults' => [
                                'controller' => PasswordControllerFactory::class,
                                'action' => 'changePassword'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'confirmation' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'action' => 'confirmation',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'username-recover' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/username-recover',
                            'defaults' => [
                                'controller' => UserProfileControllerFactory::class,
                                'action' => 'usernameRecover'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ok' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ok',
                                    'defaults' => [
                                        'controller' => UserProfileControllerFactory::class,
                                        'action' => 'usernameRecoverOk'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-qualification-status' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change-qualification-status/:vehicleClassGroup',
                            'defaults' => [
                                'controller' => ChangeQualificationStatusController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'confirmation' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/confirmation',
                                    'defaults' => [
                                        'controller' => ChangeQualificationStatusController::class,
                                        'action' => 'confirmation',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'change-person-name' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/name/change',
                            'defaults' => [
                                'controller' => ChangeNameController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'change-person-date-of-birth' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/date-of-birth',
                            'defaults' => [
                                'controller' => ChangeDateOfBirthController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'change-telephone-number' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/telephone/change',
                            'defaults' => [
                                'controller' => ChangeTelephoneController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            ContextProvider::AE_PARENT_ROUTE => [
                'type' => 'segment',
                'options' => [
                    'route' => '/authorised-examiner/[:authorisedExaminerId]/user/[:id]',
                    'defaults' => [
                        'controller' => PersonProfileController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'address' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/address',
                            'defaults' => [
                                'controller' => ChangeAddressController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'change-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/change',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'review-address' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/review',
                                    'defaults' => [
                                        'controller' => ChangeAddressController::class,
                                        'action' => 'review',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'AEEmail' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/email/change',
                            'defaults' => [
                                'controller' => EmailAddressController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may-terminate' => true,
                    ],
                    'driving-licence-change' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/driving-licence',
                            'defaults' => [
                                'controller' => DrivingLicenceControllerFactory::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'summary' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/summary',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'summary'
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'controller' => DrivingLicenceControllerFactory::class,
                                        'action' => 'delete'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'trade-roles'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/trade-roles',
                            'defaults'    => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'annual-assessment-certificates' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/annual-assessment-certificates',
                            'defaults'    => [
                                'controller' => ViewAnnualAssessmentCertificatesController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults'    => [
                                        'controller' => AddAnnualAssessmentCertificatesController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => AddAnnualAssessmentCertificatesController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/:certificateId/edit',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+'
                                    ],
                                    'defaults'    => [
                                        'controller' => EditAnnualAssessmentCertificatesController::class,
                                        'action'     => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => AddAnnualAssessmentCertificatesController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/remove/:certificateId',
                                    'constraints' => [
                                        'group' => 'A|B',
                                        'certificateId' => '[0-9]+',
                                    ],
                                    'defaults'    => [
                                        'controller' => RemoveAnnualAssessmentCertificatesController::class,
                                        'action'     => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'qualification-details' => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/qualification-details',
                            'defaults'    => [
                                'controller' => QualificationDetailsController::class,
                                'action' => 'view',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => '/:group/add',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults'    => [
                                        'controller' => QualificationDetailsController::class,
                                        'action'     => 'add',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addReview',
                                            ],
                                        ],
                                    ],
                                    'add-confirmation' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/confirmation',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'addConfirmation',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/edit',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'review' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/review/:formUuid',
                                            'defaults' => [
                                                'controller' => QualificationDetailsController::class,
                                                'action' => 'editReview',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'remove' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:group/remove',
                                    'constraints' => [
                                        'group' => 'a|b',
                                    ],
                                    'defaults' => [
                                        'controller' => QualificationDetailsController::class,
                                        'action' => 'remove',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'remove-vts-role' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/remove-vts-role/:entityId/:positionId',
                            'constraints' => [
                                'entityId'   => '[0-9]+',
                                'positionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => UserTradeRolesController::class,
                                'action' => 'removeVtsRole'
                            ]
                        ]
                    ],
                    'test-quality-information' => [
                        'may_terminate' => true,
                        'type' => 'segment',
                        'options' => [
                            'route' => '/test-quality-information/:month/:year',
                            'constraints' => [
                                'month' => '[0-9]+',
                                'year' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => TestQualityInformationController::class,
                                'action' => 'testQualityInformation',
                            ],
                        ],
                        'child_routes' => [
                            'component-breakdown' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/components/:group',
                                    'constraints' => [
                                        'group' => 'A|B',
                                    ],
                                    'defaults' => [
                                        'controller' => TestQualityInformationController::class,
                                        'action' => 'componentBreakdown',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-password'              => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'    => '/change-password',
                            'defaults' => [
                                'controller' => PasswordControllerFactory::class,
                                'action' => 'changePassword'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'confirmation' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => '/confirmation',
                                    'defaults' => [
                                        'action' => 'confirmation',
                                    ],
                                ],
                            ],
                        ],

                    ],
                    'username-recover' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/username-recover',
                            'defaults' => [
                                'controller' => UserProfileControllerFactory::class,
                                'action' => 'usernameRecover'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ok' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ok',
                                    'defaults' => [
                                        'controller' => UserProfileControllerFactory::class,
                                        'action' => 'usernameRecoverOk'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'change-person-date-of-birth' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/date-of-birth',
                            'defaults' => [
                                'controller' => ChangeDateOfBirthController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'change-qualification-status' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change-qualification-status/:vehicleClassGroup',
                            'defaults' => [
                                'controller' => ChangeQualificationStatusController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'confirmation' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/confirmation',
                                    'defaults' => [
                                        'controller' => ChangeQualificationStatusController::class,
                                        'action' => 'confirmation',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'change-person-name' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/name/change',
                            'defaults' => [
                                'controller' => ChangeNameController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'change-telephone-number' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/telephone/change',
                            'defaults' => [
                                'controller' => ChangeTelephoneController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
];
