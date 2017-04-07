<?php

use Account\Factory\Controller\ClaimAccountControllerFactory;
use Account\Factory\Controller\PasswordResetControllerFactory;
use Account\Factory\Controller\SecurityQuestionControllerFactory;

return [
    'router' => [
        'routes' => [
            'account' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/account',
                    'defaults' => [
                        'controller'    => 'Account\Controller\Index',
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'claim' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/claim',
                            'defaults' => [
                                'controller' => ClaimAccountControllerFactory::class,
                                'action'     => 'confirmPassword',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'confirmPassword' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/confirm-email-and-password',
                                    'defaults' => [
                                        'controller' => ClaimAccountControllerFactory::class,
                                        'action'     => 'confirmPassword',
                                    ],
                                ],
                                'may_terminate' => true
                            ],
                            'setSecurityQuestion' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/set-security-question',
                                    'defaults' => [
                                        'controller' => ClaimAccountControllerFactory::class,
                                        'action'     => 'setSecurityQuestion',
                                    ],
                                ],
                                'may_terminate' => true
                            ],
                            'success' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/success',
                                    'defaults' => [
                                        'controller' => ClaimAccountControllerFactory::class,
                                        'action'     => 'success',
                                    ],
                                ],
                                'may_terminate' => true
                            ],
                            'review' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/review',
                                    'defaults' => [
                                        'controller' => ClaimAccountControllerFactory::class,
                                        'action'     => 'review',
                                    ],
                                ],
                                'may_terminate' => true
                            ],
                            'reset' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/reset',
                                    'defaults' => [
                                        'controller' => ClaimAccountControllerFactory::class,
                                        'action'     => 'reset',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'forgotten-password' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/forgotten-password',
                    'defaults' => [
                        'controller' => PasswordResetControllerFactory::class,
                        'action' => 'username'
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'security-question' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/security-question/:personId/:questionNumber',
                            'constraints' => [
                                'personId'       => '[0-9]+',
                                'questionNumber' => '1|2',
                            ],
                            'defaults' => [
                                'controller' => SecurityQuestionControllerFactory::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true
                    ],
                    'authenticated' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/authenticated',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action' => 'authenticated'
                            ],
                        ],
                        'may_terminate' => true
                    ],
                    'notAuthenticated' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/not-authenticated',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action' => 'notAuthenticated'
                            ],
                        ],
                        'may_terminate' => true
                    ],
                    'emailNotFound' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/email-not-found',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action' => 'emailNotFound'
                            ],
                        ],
                        'may_terminate' => true
                    ],
                    'confirmationEmail' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route'    => '/confirmation-email',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action' => 'confirmation'
                            ],
                        ],
                        'may_terminate' => true
                    ],
                    'reset-password'     => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/reset[/:resetToken]',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action'     => 'changePassword',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'update-password'     => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/update',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action'     => 'updatePassword',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'password-changed-confirmation'     => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/confirmation[/:resetToken]',
                            'defaults' => [
                                'controller' => PasswordResetControllerFactory::class,
                                'action'     => 'passwordChangedConfirmation',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Account\Controller\Index'  => 'Account\Controller\IndexController',
        ],
        'factories' => [
            ClaimAccountControllerFactory::class     => ClaimAccountControllerFactory::class,
            PasswordResetControllerFactory::class    => PasswordResetControllerFactory::class,
            SecurityQuestionControllerFactory::class => SecurityQuestionControllerFactory::class,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ]
];
