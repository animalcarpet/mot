<?php

use PersonApi\Controller\AuthorisedExaminerController;
use PersonApi\Controller\DashboardController;
use PersonApi\Controller\MotTestingAuthorisationController;
use PersonApi\Controller\PersonalDetailsController;
use PersonApi\Controller\PersonAuthorisationController;
use PersonApi\Controller\PersonByLoginController;
use PersonApi\Controller\PersonController;
use PersonApi\Controller\PersonCurrentMotTestController;
use PersonApi\Controller\PersonProfileRestrictedController;
use PersonApi\Controller\PersonProfileUnrestrictedController;
use PersonApi\Controller\PersonSiteCountController;
use PersonApi\Controller\ResetClaimAccountController;
use PersonApi\Controller\ResetPinController;
use PersonApi\Controller\UserStatsController;
use UserApi\SpecialNotice\Controller\SpecialNoticeController;

return [
    'routes' => [
        'personal-details' => [
            'type'    => 'Segment',
            'options' => [
                'route'       => '/personal-details/:id',
                'constraints' => [
                    'id' => '[0-9]+',
                ],
                'defaults'    => [
                    'controller' => PersonalDetailsController::class,
                ],
            ]
        ],
        'person'           => [
            'type'          => 'segment',
            'options'       => [
                'route'       => '/person[/:id]',
                'defaults'    => [
                    'controller' => PersonController::class,
                ],
                'constraints' => [
                    'id' => '[0-9]+'
                ],
            ],
            'may_terminate' => true,
            'child_routes'  => [
                'help-desk-reset-claim-account'  => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/reset-claim-account',
                        'defaults' => [
                            'controller' => ResetClaimAccountController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'by-login'                       => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'       => '/username/:login',
                        'defaults'    => [
                            'controller' => PersonByLoginController::class,
                        ],
                        'constraints' => [
                            'login' => '[a-zA-Z]?[a-zA-Z0-9\.\-_@]*'
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'help-desk-profile-restricted'   => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/help-desk-profile-restricted',
                        'defaults' => [
                            'controller' => PersonProfileRestrictedController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'help-desk-profile-unrestricted' => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/help-desk-profile-unrestricted',
                        'defaults' => [
                            'controller' => PersonProfileUnrestrictedController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'rbac-roles'                     => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/rbac-roles',
                        'defaults' => [
                            'controller' => PersonAuthorisationController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'dashboard'                      => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/dashboard',
                        'defaults' => [
                            'controller' => DashboardController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'current-mot-test'               => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/current-mot-test',
                        'defaults' => [
                            'controller' => PersonCurrentMotTestController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'site-count'                     => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/site-count',
                        'defaults' => [
                            'controller' => PersonSiteCountController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'mot-testing'                    => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/mot-testing',
                        'defaults' => [
                            'controller' => MotTestingAuthorisationController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'stats'                          => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/stats',
                        'defaults' => [
                            'controller' => UserStatsController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'authorised-examiner'            => [
                    'type'    => 'segment',
                    'options' => [
                        'route'    => '/authorised-examiner',
                        'defaults' => [
                            'controller' => AuthorisedExaminerController::class
                        ]
                    ]
                ],
                'special-notice'                 => [
                    'type'    => 'Segment',
                    'options' => [
                        'route'    => '/special-notice[/:snId]',
                        'defaults' => [
                            'controller' => SpecialNoticeController::class
                        ]
                    ]
                ],
                'reset-pin'                      => [
                    'type'          => 'segment',
                    'options'       => [
                        'route'    => '/reset-pin',
                        'defaults' => [
                            'controller' => ResetPinController::class,
                        ],
                    ],
                    'may_terminate' => true,
                ],
            ],
        ],
    ],
];
