<?php

use TestSupport\Controller;
use TestSupport\Controller\GdsSurveyController;

return [
    'controllers'  => [
        'invokables' => [
            Controller\FeaturesController::class            => Controller\FeaturesController::class,
            Controller\IndexController::class               => Controller\IndexController::class,
            Controller\ResetController::class               => Controller\ResetController::class,
            Controller\TesterDataController::class          => Controller\TesterDataController::class,
            Controller\InactiveTesterDataController::class  => Controller\InactiveTesterDataController::class,
            Controller\SchemeMgtUserDataController::class   => Controller\SchemeMgtUserDataController::class,
            Controller\SchemeUserDataController::class      => Controller\SchemeUserDataController::class,
            Controller\AeDataController::class              => Controller\AeDataController::class,
            Controller\OrganisationRoleDataController::class             => Controller\OrganisationRoleDataController::class,
            Controller\AedmDataController::class            => Controller\AedmDataController::class,
            Controller\VtsDataController::class             => Controller\VtsDataController::class,
            Controller\SiteManagerDataController::class     => Controller\SiteManagerDataController::class,
            Controller\SiteAdminDataController::class       => Controller\SiteAdminDataController::class,
            Controller\AreaOffice1DataController::class     => Controller\AreaOffice1DataController::class,
            Controller\AreaOffice2DataController::class     => Controller\AreaOffice2DataController::class,
            Controller\FinanceUserController::class         => Controller\FinanceUserController::class,
            Controller\VehicleExaminerDataController::class => Controller\VehicleExaminerDataController::class,
            Controller\VM10519UserDataController::class     => Controller\VM10519UserDataController::class,
            Controller\VM10619RoleManagementUpgradeController::class    => Controller\VM10619RoleManagementUpgradeController::class,
            Controller\AssessorDataController::class        => Controller\AssessorDataController::class,
            Controller\UserDataController::class            => Controller\UserDataController::class,
            Controller\TestSupportMotTestController::class  => Controller\TestSupportMotTestController::class,
            Controller\RouteDumpController::class           => Controller\RouteDumpController::class,
            Controller\CacheController::class               => Controller\CacheController::class,
            Controller\VehicleDataController::class         => Controller\VehicleDataController::class,
            Controller\SpecialNoticeDataController::class   => Controller\SpecialNoticeDataController::class,
            Controller\CustomerServiceCentreOperativeDataController::class
                => Controller\CustomerServiceCentreOperativeDataController::class,
            Controller\CustomerServiceManagerDataController::class     => Controller\CustomerServiceManagerDataController::class,
            Controller\DvlaOperativeDataController::class   => Controller\DvlaOperativeDataController::class,
            Controller\EventDataController::class           => Controller\EventDataController::class,
            Controller\SecurityQuestionDataController::class=> Controller\SecurityQuestionDataController::class,
            Controller\PasswordResetDataController::class   => Controller\PasswordResetDataController::class,
            Controller\SlotTransactionController::class     =>   Controller\SlotTransactionController::class,
            Controller\DvlaVehicleDataController::class => Controller\DvlaVehicleDataController::class,
            Controller\TesterAuthorisationStatusController::class => Controller\TesterAuthorisationStatusController::class,
            Controller\DocumentController::class            => Controller\DocumentController::class,
            Controller\PersonRoleController::class          => Controller\PersonRoleController::class,
            Controller\SiteRoleController::class            => Controller\SiteRoleController::class,
            Controller\TwoFactorAuthCardController::class   => Controller\TwoFactorAuthCardController::class,
            Controller\QualificationDetailsController::class => Controller\QualificationDetailsController::class,
            Controller\StatisticsAmazonCacheController::class => Controller\StatisticsAmazonCacheController::class,
            Controller\ClaimAccountController::class => Controller\ClaimAccountController::class,
            Controller\CatUserController::class => Controller\CatUserController::class,
            Controller\SiteRoleNominationController::class => Controller\SiteRoleNominationController::class,
            Controller\OrganisationRoleNominationController::class => Controller\OrganisationRoleNominationController::class,
            Controller\AnnualAssessmentCertificateController::class => Controller\AnnualAssessmentCertificateController::class,
            Controller\OneHundredMotTestsController::class => Controller\OneHundredMotTestsController::class,
            GdsSurveyController::class => GdsSurveyController::class,
        ],
    ],
    'router'       => [
        'routes' => [
            'index'       => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                    ],
                ],
            ],
            'reset'       => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/reset',
                    'defaults' => [
                        'controller' => Controller\ResetController::class,
                    ],
                ],
            ],
            'testsupport' => [
                'type'         => 'Segment',
                'options'      => [
                    'route' => '/testsupport',
                ],
                'child_routes' => [
                    'claimAccountSub' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/claimaccount',
                            'defaults' => [
                                'controller' => Controller\ClaimAccountController::class
                            ],
                        ],
                    ],
                    'testerSub'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/tester',
                            'defaults' => [
                                'controller' => Controller\TesterDataController::class
                            ],
                        ],
                    ],
                    'generate-survey' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/survey/reports/generate',
                            'defaults' => [
                                'controller' => GdsSurveyController::class,
                            ],
                        ],
                    ],
                    'inactiveTesterSub'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/inactivetester',
                            'defaults' => [
                                'controller' => Controller\InactiveTesterDataController::class
                            ],
                        ],
                    ],
                    'catUserSub'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/catuser',
                            'defaults' => [
                                'controller' => Controller\CatUserController::class
                            ],
                        ],
                    ],
                    'featuresSub'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/features/:featureName',
                            'defaults' => [
                                'controller' => Controller\FeaturesController::class
                            ],
                        ],
                    ],
                    'schmSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/schm',
                            'defaults' => [
                                'controller' => Controller\SchemeMgtUserDataController::class
                            ],
                        ],
                    ],
                    'schuserSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/schemeuser',
                            'defaults' => [
                                'controller' => Controller\SchemeUserDataController::class
                            ],
                        ],
                    ],
                    'aeSub'              => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/ae',
                            'defaults' => [
                                'controller' => Controller\AeDataController::class
                            ],
                        ],
                    ],
                    'aedSub'             => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/aed',
                            'defaults' => [
                                'controller' => Controller\OrganisationRoleDataController::class
                            ],
                        ],
                    ],
                    'aedmSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/aedm',
                            'defaults' => [
                                'controller' => Controller\AedmDataController::class
                            ],
                        ],
                    ],
                    'financeSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/financeuser',
                            'defaults' => [
                                'controller' => Controller\FinanceUserController::class
                            ],
                        ],
                    ],
                    'motTestSub'         => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/mottest',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults'    => [
                                'controller' => Controller\TestSupportMotTestController::class
                            ],
                        ],
                    ],
                    'oneHundredMotTestsSub'         => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/onehundredmottests',
                            'defaults'    => [
                                'controller' => Controller\OneHundredMotTestsController::class
                            ],
                        ],
                    ],
                    'siteSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/vts',
                            'defaults' => [
                                'controller' => Controller\VtsDataController::class
                            ],
                        ],
                    ],
                    'siteManagerSub'     => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/vts/sm',
                            'defaults' => [
                                'controller' => Controller\SiteManagerDataController::class
                            ],
                        ],
                    ],
                    'siteAdminSub'       => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/vts/sa',
                            'defaults' => [
                                'controller' => Controller\SiteAdminDataController::class
                            ],
                        ],
                    ],
                    'areaAdminSub'       => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/areaoffice1',
                            'defaults' => [
                                'controller' => Controller\AreaOffice1DataController::class
                            ],
                        ],
                    ],
                    'areaOffice2Sub'       => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/areaoffice2',
                            'defaults' => [
                                'controller' => Controller\AreaOffice2DataController::class
                            ],
                        ],
                    ],
                    'vehicleExaminerSub' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/vehicleexaminer',
                            'defaults' => [
                                'controller' => Controller\VehicleExaminerDataController::class
                            ],
                        ],
                    ],
                    'superVehicleExaminerSub' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/vm10519user',
                            'defaults' => [
                                'controller' => Controller\VM10519UserDataController::class
                            ],
                        ],
                    ],
                    'vm10619rolemanagementupgrade' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/vm10619rolemanagementupgrade',
                            'defaults' => [
                                'controller' => Controller\VM10619RoleManagementUpgradeController::class
                            ],
                        ],
                    ],
                    'assessorSub'        => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/assessor',
                            'defaults' => [
                                'controller' => Controller\AssessorDataController::class
                            ],
                        ],
                    ],
                    'userSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/user',
                            'defaults' => [
                                'controller' => Controller\UserDataController::class
                            ],
                        ],
                    ],
                    'cscoSub'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/csco',
                            'defaults' => [
                                'controller' => Controller\CustomerServiceCentreOperativeDataController::class,
                            ],
                        ],
                    ],
                    'csmSub'             => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/csm',
                            'defaults' => [
                                'controller' => Controller\CustomerServiceManagerDataController::class,
                            ],
                        ],
                    ],
                    'dvlaOperative'            => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/dvlaoperative',
                            'defaults' => [
                                'controller' => Controller\DvlaOperativeDataController::class,
                            ],
                        ],
                    ],
                    'routesSub'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/routes',
                            'defaults' => [
                                'controller' => Controller\RouteDumpController::class
                            ],
                        ],
                    ],
                    'vehicleSub'         => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/vehicle',
                        ],
                        'child_routes' => [
                            'v5cSub' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/v5c-add',
                                    'defaults' => [
                                        'controller' => Controller\VehicleDataController::class,
                                        'action' => 'v5cAdd'
                                    ],
                                ],
                            ],
                            'create-vehicle' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/create',
                                    'defaults' => [
                                        'controller' => Controller\VehicleDataController::class,
                                        'action' => 'create'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dvlaVehicle'         => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/dvla-vehicle',
                        ],
                        'child_routes' => [
                            'create-vehicle' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/create',
                                    'defaults' => [
                                        'controller' => Controller\DvlaVehicleDataController::class,
                                        'action' => 'create'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'specialNoticeSub' => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/special-notice',
                        ],
                        'child_routes' => [
                            'broadcastSub' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/broadcast',
                                    'defaults' => [
                                        'controller' => Controller\SpecialNoticeDataController::class,
                                        'action' => 'broadcast'
                                    ],
                                ],
                            ],
                            'createSub' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/create',
                                    'defaults' => [
                                        'controller' => Controller\SpecialNoticeDataController::class,
                                        'action' => 'create'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'eventSub'         => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/event/create',
                            'defaults' => [
                                'controller' => Controller\EventDataController::class,
                            ],
                        ],
                    ],
                    'clearStatisticsAmazonCache' => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/clear-statistics-amazon-cache',
                            'defaults' => [
                                'controller' => Controller\StatisticsAmazonCacheController::class,
                                'action' => 'removeAll'
                            ],
                        ],
                    ],
                    'qualificationDetailsSub' => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/qualification-details',
                            'defaults' => [
                                'controller' => Controller\QualificationDetailsController::class,
                            ],
                        ],
                    ],
                    'annualExamSub' => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/annual-assessment-certificate',
                            'defaults' => [
                                'controller' => Controller\AnnualAssessmentCertificateController::class,
                            ],
                        ],
                    ],
                    'securityQuestionSub'         => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/security-question/create',
                            'defaults' => [
                                'controller' => Controller\SecurityQuestionDataController::class,
                            ],
                        ],
                    ],
                    'resetPasswordTokenSub' => [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/reset-password',
                            'defaults' => [
                                'controller' => Controller\PasswordResetDataController::class,
                            ],
                        ],
                    ],
                    'slotTransaction'=> [
                        'type'         => 'Segment',
                        'options'      => [
                            'route' => '/slot-transaction',
                            'defaults' => [
                                'controller' => Controller\SlotTransactionController::class,
                            ],
                        ],
                    ],
                    'testerAuthorisationStatus' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/tester-authorisation-status',
                            'defaults' => [
                                'controller' => Controller\TesterAuthorisationStatusController::class
                            ],
                        ],
                    ],
                    'document' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/document[/:id]/',
                            'defaults' => [
                                'controller' => Controller\DocumentController::class
                            ]
                        ]
                    ],
                    'person'           => [
                        'type'          => 'segment',
                        'options'       => [
                            'route'       => '/person/:id',
                            'constraints' => [
                                'id' => '[0-9]+'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add_role' => [
                                'verb'    => 'put',
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/role/:role',
                                    'defaults'    => [
                                        'controller' => Controller\PersonRoleController::class,
                                    ],
                                ]
                            ],
                            'add_site_role' => [
                                'verb'    => 'put',
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/site/:site/site-role/:role',
                                    'defaults'    => [
                                        'controller' => Controller\SiteRoleController::class
                                    ],
                                ]
                            ],
                            'delete_role' => [
                                'verb'    => 'delete',
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/roles/:role',
                                    'constraints' => [
                                        'role' => '[A-Z0-9-]+',
                                    ],
                                    'defaults'    => [
                                        'controller' => Controller\PersonRoleController::class,
                                    ],
                                ]
                            ],
                        ],
                    ],
                    'twoFactorAuthSub'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/two-factor-auth/card',
                            'defaults' => [
                                'controller' => Controller\TwoFactorAuthCardController::class
                            ],
                        ],
                    ],
                    'site-role-nomination'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/site/role/nomination',
                            'defaults' => [
                                'controller' => Controller\SiteRoleNominationController::class
                            ],
                        ],
                    ],
                    'role-nomination'          => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/org/role/nomination',
                            'defaults' => [
                                'controller' => Controller\OrganisationRoleNominationController::class
                            ],
                        ],
                    ],
                ],
            ],
            'cache'       => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/cache',
                    'defaults' => [
                        'controller' => Controller\CacheController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'apc' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/apc',
                            'verb'     => 'delete',
                            'defaults' => [
                                'action' => 'apc-cache-clear'
                            ],
                        ],
                    ],
                    'op'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/op',
                            'verb'     => 'delete',
                            'defaults' => [
                                'action' => 'op-cache-clear'
                            ],
                        ],
                    ],
                    'mot-api'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/mot-api',
                            'verb'     => 'delete',
                            'defaults' => [
                                'action' => 'mot-api-cache-clear'
                            ],
                        ],
                    ],
                    'mot-web-frontend'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/mot-web-frontend',
                            'verb'     => 'delete',
                            'defaults' => [
                                'action' => 'mot-web-frontend-cache-clear'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
