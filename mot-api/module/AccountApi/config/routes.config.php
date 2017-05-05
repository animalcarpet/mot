<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

use AccountApi\Controller\ClaimController;
use AccountApi\Controller\SecurityQuestionController;
use AccountApi\Factory\Controller\PasswordChangeControllerFactory;
use AccountApi\Factory\Controller\PasswordResetControllerFactory;
use AccountApi\Factory\Controller\PasswordUpdateControllerFactory;
use AccountApi\Factory\Controller\ValidateUsernameControllerFactory;
use Zend\Http\Request;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Method;
use Zend\Mvc\Router\Http\Segment;

return [
    'AccountApi' => [
        'type' => Literal::class,
        'options' => [
            'route' => '/account',
            'defaults' => [
                'controller' => 'Index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'password-change' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/password-change',
                    'defaults' => [
                        '__NAMESPACE__' => 'AccountApi\Factory\Controller',
                        'controller' => PasswordChangeControllerFactory::class,
                    ],
                ],
                'may_terminate' => true,
            ],
            'password-update' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/password-update/:id',
                    'defaults' => [
                        '__NAMESPACE__' => 'AccountApi\Factory\Controller',
                        'controller' => PasswordUpdateControllerFactory::class,
                    ],
                ],
                'may_terminate' => true,
            ],
            'default' => [
                'type' => Segment::class,
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
        'type' => Literal::class,
        'options' => [
            'route' => '/reset-password',
            'defaults' => [
                'controller' => PasswordResetControllerFactory::class,
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'validate-token' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:token',
                    'defaults' => [
                        'controller' => PasswordResetControllerFactory::class,
                    ],
                ],
            ],
            'validate-username' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/validate-username[/:login]',
                    'defaults' => [
                        'controller' => ValidateUsernameControllerFactory::class,
                    ],
                ],
            ],
        ],
    ],
    'security-question' => [
        'type' => Segment::class,
        'options' => [
            'route' => '/security-question',
            'defaults' => [
                'controller' => SecurityQuestionController::class,
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'update' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:id',
                    'constraints' => [
                        'uid' => '[0-9]+',
                    ],
                    'verb' => 'put',
                    'defaults' => [
                        'controller' => SecurityQuestionController::class,
                    ],
                ],
            ],
            'check' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/check/:qid/:uid',
                    'constraints' => [
                        'qid' => '[0-9]+',
                        'uid' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SecurityQuestionController::class,
                        'action' => 'verifyAnswer'
                    ],
                ],
            ],
            'get' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/get/:qid/:uid',
                    'constraints' => [
                        'qid' => '[0-9]+',
                        'uid' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SecurityQuestionController::class,
                        'action' => 'getQuestionForPerson'
                    ],
                ],
            ],
        ],
    ],
    'person-security-questions' => [
        'type' => Segment::class,
        'options' => [
            'route' => '/person/:personId',
            'constraints' => [
                'personId' => '[0-9]+',
            ],
        ],
        'may_terminate' => false,
        'child_routes' => [
            'get-person-questions' => [
                'type' => Method::class,
                'options' => [
                    'verb' => Request::METHOD_GET,
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'get-person-questions' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/security-questions',
                            'defaults' => [
                                'controller' => SecurityQuestionController::class,
                                'action' => 'getQuestionsForPerson'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'post-person-answers' => [
                'type' => Method::class,
                'options' => [
                    'verb' => Request::METHOD_POST,
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'verify-answers' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/security-questions/verify',
                            'defaults' => [
                                'controller' => SecurityQuestionController::class,
                                'action' => 'verifyAnswers'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
];
