<?php

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\AlreadyHasRegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardHardStopController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardSuccessController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\AlreadyOrderedNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderAddressController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderConfirmationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderReviewController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\OrderNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller\CardOrderCsvReportController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller\CardOrderReportListController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\ForgotSecurityQuestionController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\RegisterCardInformationNewUserController;

return [
    'router' => [
        'routes' => [
            'security-card-order' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/security-card-order',
                    'defaults' => [
                        'controller' => OrderNewCardController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'new' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/new[/:userId]',
                            'defaults' => [
                                'controller' => OrderNewCardController::class,
                            ],
                        ],
                    ],
                    'address' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/address[/:userId]',
                            'defaults' => [
                                'controller' => CardOrderAddressController::class,
                            ],
                        ],
                    ],
                    'review' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/review[/:userId]',
                            'defaults' => [
                                'controller' => CardOrderReviewController::class,
                            ],
                        ],
                    ],
                    'confirmation' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/confirmation[/:userId]',
                            'defaults' => [
                                'controller' => CardOrderConfirmationController::class,
                            ],
                        ],
                    ],
                    'already-ordered' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/already-ordered',
                            'defaults' => [
                                'controller' => AlreadyOrderedNewCardController::class,
                            ],
                        ],
                    ]
                ]
            ],

            'register-card' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/register-card',
                    'defaults' => [
                        'controller' => RegisterCardController::class,
                        'action' => 'register'
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'success' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/success',
                            'defaults' => [
                                'controller' => RegisterCardSuccessController::class,
                                'action'     => 'success'
                            ],
                        ],
                    ],
                    'already-has-card' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/already-has-card',
                            'defaults' => [
                                'controller' => AlreadyHasRegisteredCardController::class,
                                'action'     => 'index'
                            ],
                        ],
                    ],
                    'hard-stop' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/hard-stop',
                            'defaults' => [
                                'controller' => RegisterCardHardStopController::class,
                                'action'     => 'index'
                            ],
                        ],
                    ],
                ],
            ],
            'login-2fa' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/login-2fa',
                    'defaults' => [
                        'controller' => RegisteredCardController::class,
                        'action' => 'login2FA',
                    ],
                ],
            ],

            RegisteredCardController::ROUTE_2FA_LOCKED_OUT => [
                'type' => 'literal',
                'options' => [
                    'route' => '/'.RegisteredCardController::ROUTE_2FA_LOCKED_OUT,
                    'defaults' => [
                        'controller' => RegisteredCardController::class,
                        'action' => 'pinFailLocked',
                    ],
                ],
            ],

            RegisteredCardController::ROUTE_2FA_LOCKOUT_WARN => [
                'type' => 'literal',
                'options' => [
                    'route' => '/'.RegisteredCardController::ROUTE_2FA_LOCKOUT_WARN,
                    'defaults' => [
                        'controller' => RegisteredCardController::class,
                        'action' => 'pinLockoutWarn',
                    ],
                ],
            ],



            'security-card-information' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/security-card-information/:userId',
                    'constraints' => [
                        'userId'   => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => RegisterCardInformationController::class,
                        'action' => 'registerCardInformation',
                    ],
                ],
            ],
            'security-card-information-new-user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/security-card-information-new-user/:userId',
                    'constraints' => [
                        'userId'   => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => RegisterCardInformationNewUserController::class,
                        'action' => 'registerCardInformationNewUser',
                    ],
                ],
            ],
            'order-card-new-user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/order-card-new-user/:userId',
                    'constraints' => [
                        'userId'   => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => NewUserOrderCardController::class,
                        'action' => 'orderCardNewUser',
                    ],
                ],
            ],
            'security-card-order-report-list' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/security-card-order-report-list',
                    'defaults' => [
                        'controller' => CardOrderReportListController::class,
                        'action'     => 'list',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'download-csv' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/:date/download-csv',
                            'constraints' => [
                                'date'   => '[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z',
                            ],
                            'defaults' => [
                                'controller' => CardOrderCsvReportController::class,
                                'action'     => 'downloadCsv'
                            ],
                        ],
                    ]
                ]
            ],
            'lost-or-forgotten-card' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/lost-or-forgotten-card',
                    'defaults' => [
                        'controller' => LostOrForgottenCardController::class,
                        'action'     => 'start',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'already-ordered' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/already-ordered',
                            'defaults' => [
                                'controller' => LostOrForgottenCardController::class,
                                'action'     => 'startAlreadyOrdered'
                            ],
                        ],
                    ],
                    'question-one' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/question-one',
                            'defaults' => [
                                'controller' => LostOrForgottenCardController::class,
                                'action'     => 'securityQuestionOne'
                            ],
                        ],
                    ],
                    'question-two' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/question-two',
                            'defaults' => [
                                'controller' => LostOrForgottenCardController::class,
                                'action'     => 'securityQuestionTwo'
                            ],
                        ],
                    ],
                    'confirmation' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/confirmation',
                            'defaults' => [
                                'controller' => LostOrForgottenCardController::class,
                                'action'     => 'confirmation'
                            ],
                        ],
                    ],
                    'forgot-question' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => '/forgot-question',
                            'defaults' => [
                                'controller' => ForgotSecurityQuestionController::class,
                                'action'     => 'forgotQuestionAnswer'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
