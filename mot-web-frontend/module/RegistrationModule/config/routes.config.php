<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Frontend\RegistrationModule\Controller\ContactDetailsController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\CompletedController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\CreateAccountController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\DetailsController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\DuplicateEmailController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\IndexController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\PasswordController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\SecurityQuestionsController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\SummaryController;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\EmailController;

/*
 * The Registration module is a multi-page from and the following routes are sorted in the same order as the user journey,
 * and not alphabetically.
 *
 * http://mot-web-frontend.mot.gov.uk/account/register/create-an-account
 * http://mot-web-frontend.mot.gov.uk/account/register/email
 * http://mot-web-frontend.mot.gov.uk/account/register/details
 * http://mot-web-frontend.mot.gov.uk/account/register/contact-details
 * http://mot-web-frontend.mot.gov.uk/account/register/security-questions
 * http://mot-web-frontend.mot.gov.uk/account/register/password
 * http://mot-web-frontend.mot.gov.uk/account/register/summary
 * http://mot-web-frontend.mot.gov.uk/account/register/completed
 */
return [
    'router' => [
        'routes' => [
            'account-register' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/account/register',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'create-an-account' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/create-an-account[/]',
                            'defaults' => [
                                'controller' => CreateAccountController::class,
                            ],
                        ],
                    ],
                    'email' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/email[/]',
                            'defaults' => [
                                'controller' => EmailController::class,
                            ],
                        ],
                    ],
                    'details' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/details[/]',
                            'defaults' => [
                                'controller' => DetailsController::class,
                            ],
                        ],
                    ],
                    'address' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/contact-details[/]',
                            'defaults' => [
                                'controller' => ContactDetailsController::class,
                            ],
                        ],
                    ],
                    'security-questions' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/security-questions[/]',
                            'defaults' => [
                                'controller' => SecurityQuestionsController::class,
                            ],
                        ],
                    ],
                    'password' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/password[/]',
                            'defaults' => [
                                'controller' => PasswordController::class,
                            ],
                        ],
                    ],
                    'summary' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/summary[/]',
                            'defaults' => [
                                'controller' => SummaryController::class,
                            ],
                        ],
                    ],
                    'complete' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/complete',
                            'defaults' => [
                                'controller' => CompletedController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'complete-registration-success' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/success',
                            'defaults' => [
                                'controller' => CompletedController::class,
                                'action' => 'success',
                            ],
                        ],
                    ],
                    'complete-registration-failure' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/fail',
                            'defaults' => [
                                'controller' => CompletedController::class,
                                'action' => 'fail',
                            ],
                        ],
                    ],
                    'duplicate-email' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/duplicate-email',
                            'defaults' => [
                                'controller' => DuplicateEmailController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
