<?php

use AccountApi\Controller\ClaimController;
use AccountApi\Factory\Controller\PasswordResetControllerFactory;
use AccountApi\Factory\Controller\PasswordChangeControllerFactory;
use AccountApi\Factory\Controller\PasswordUpdateControllerFactory;
use AccountApi\Factory\Controller\ValidateUsernameControllerFactory;
use AccountApi\Factory\Controller\SecurityQuestionControllerFactory;

return [
    'AccountApi' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/account',
            'defaults' => [
                'controller' => 'Index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'password-change' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/password-change',
                    'defaults' => [
                        '__NAMESPACE__' => 'AccountApi\Factory\Controller',
                        'controller' => PasswordChangeControllerFactory::class,
                    ],
                ],
                'may_terminate' => true,
            ],
            'password-update' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/password-update/:id',
                    'defaults' => [
                        '__NAMESPACE__' => 'AccountApi\Factory\Controller',
                        'controller' => PasswordUpdateControllerFactory::class,
                    ],
                ],
                'may_terminate' => true,
            ],
            'default' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/claim[/:id]',
                    'constraints' => [
                        'id' => '[0-9_-]*'
                    ],
                    'defaults' => [
                        'controller' => ClaimController::class
                    ]
                ]
            ]
        ]
    ],
    'send-reset-password' => [
        'type'    => 'Literal',
        'options' => [
            'route'    => '/reset-password',
            'defaults' => [
                'controller' => PasswordResetControllerFactory::class,
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'validate-token' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/:token',
                    'defaults' => [
                        'controller' => PasswordResetControllerFactory::class,
                    ],
                ],
            ],
            'validate-username' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/validate-username[/:login]',
                    'defaults' => [
                        'controller' => ValidateUsernameControllerFactory::class,
                    ],
                ],
            ],
        ],
    ],
    'security-question' => [
        'type'          => 'Segment',
        'options'       => [
            'route'    => '/security-question',
            'defaults' => [
                'controller' => SecurityQuestionControllerFactory::class,
            ],
        ],
        'may_terminate' => true,
        'child_routes'  => [
            'update' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/:id',
                    'constraints' => [
                        'uid' => '[0-9]+',
                    ],
                    'verb' => 'put',
                    'defaults' => [
                        'controller' => SecurityQuestionControllerFactory::class,
                    ],
                ],
            ],
            'check' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/check/:qid/:uid',
                    'constraints' => [
                        'qid' => '[0-9]+',
                        'uid' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SecurityQuestionControllerFactory::class,
                        'action'     => 'verifyAnswer'
                    ],
                ],
            ],
            'get' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/get/:qid/:uid',
                    'constraints' => [
                        'qid' => '[0-9]+',
                        'uid' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SecurityQuestionControllerFactory::class,
                        'action'     => 'getQuestionForPerson'
                    ],
                ],
            ],
        ],
    ],
];
