<?php

use DvsaCommon\Validator\UsernameValidator;
use Organisation\Controller\TestQualityInformationController;
use Organisation\Controller\AuthorisedExaminerPrincipalController;
use Organisation\Controller\MotTestLogController;
use Organisation\Controller\RoleController;
use Organisation\Controller\SearchController;
use Organisation\Controller\SiteController;
use Organisation\Factory\Controller\SiteControllerFactory;
use Organisation\Factory\Controller\AuthorisedExaminerControllerFactory;
use Organisation\UpdateAeProperty\Factory\UpdateAePropertyProcessBuilderFactory;
use Organisation\UpdateAeProperty\UpdateAePropertyController;
use Organisation\UpdateAeProperty\UpdateAePropertyProcessBuilder;
use SlotPurchase\Service\DirectDebitService;
use SlotPurchase\Service\Factory\DirectDebitServiceFactory;

return [
    UsernameValidator::class => [
        'options' => [
            'max' => 50, // FIXME: This should match DvsaEntities\Entity\Person\Person::FIELD_USERNAME_LENGTH
        ],
    ],
    'router' => [
        'routes' => [
            'authorised-examiner-search' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/authorised-examiner/search',
                    'defaults' => [
                        'controller' => SearchController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
            'authorised-examiner-create' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/authorised-examiner/create',
                    'defaults' => [
                        'controller' => AuthorisedExaminerControllerFactory::class,
                        'action' => 'create',
                    ],
                ],
                'may_terminate' => true,
            ],
            'authorised-examiner' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/authorised-examiner[/:id]',
                    'constraints' => [
                        'id' => '[1-9]+[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => AuthorisedExaminerControllerFactory::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'search' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/search',
                            'defaults' => [
                                'controller' => SearchController::class,
                            ],
                        ],
                    ],
                    'create' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/create',
                            'defaults' => [
                                'controller' => AuthorisedExaminerControllerFactory::class,
                                'action' => 'create',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'confirmation' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/confirmation',
                                    'defaults' => [
                                        'controller' => AuthorisedExaminerControllerFactory::class,
                                        'action' => 'confirmation',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'test-quality-information' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/test-quality-information',
                            'defaults' => [
                                'controller' => TestQualityInformationController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'mot-test-log' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/mot-test-log',
                            'defaults' => [
                                'controller' => MotTestLogController::class,
                                'action' => 'index',

                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'download' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/csv',
                                    'defaults' => [
                                        'controller' => MotTestLogController::class,
                                        'action' => 'downloadCsv',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'roles' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/roles',
                            'defaults' => [
                                'controller' => RoleController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'list-user-roles' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:personId/list-roles',
                            'constraints' => [
                                'personId' => '[1-9]+[0-9]*',
                            ],
                            'defaults' => [
                                'controller' => RoleController::class,
                                'action' => 'listUserRoles',
                            ],
                        ],
                    ],
                    'confirm-nomination' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:nomineeId/confirm-nomination/:roleId',
                            'constraints' => [
                                'personId' => '[1-9]+[0-9]*',
                                'roleId' => '[1-9]+[0-9]*',
                            ],
                            'defaults' => [
                                'controller' => RoleController::class,
                                'action' => 'confirmNomination',
                            ],
                        ],
                    ],
                    'remove-role' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/remove-role/:roleId',
                            'constraints' => [
                                'roleId' => '[1-9]+[0-9]*',
                            ],
                            'defaults' => [
                                'controller' => RoleController::class,
                                'action' => 'remove',
                            ],
                        ],
                    ],
                    'remove-role-confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:personId/remove-role-confirmation',
                            'constraints' => [
                                'personId' => '[1-9]+[0-9]*',
                            ],
                            'defaults' => [
                                'controller' => RoleController::class,
                                'action' => 'removeConfirmation',
                            ],
                        ],
                    ],
                    'create-principal' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/add-principal',
                            'defaults' => [
                                'controller' => AuthorisedExaminerPrincipalController::class,
                                'action' => 'create',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'review-principal' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/review[/:formUuid]',
                                    'defaults' => [
                                        'action' => 'review',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'remove-principal-confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:principalId/remove-principal-confirmation',
                            'constraints' => [
                                'principalId' => '[1-9]+[0-9]*',
                            ],
                            'defaults' => [
                                'controller' => AuthorisedExaminerPrincipalController::class,
                                'action' => 'removeConfirmation',
                            ],
                        ],
                    ],
                    'site' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/site',
                            'defaults' => [
                                'controller' => SiteController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'link' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/link',
                                    'defaults' => [
                                        'action' => 'link',
                                    ],
                                ],
                            ],
                            'unlink' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/unlink[/:linkId]',
                                    'defaults' => [
                                        'action' => 'unlink',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'authorised-examiner-edit-property' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/authorised-examiner/:id/:propertyName/change',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'propertyName' => 'name|trading-name|business-type|status|areaoffice'.
                            '|registered-address|registered-email|registered-telephone'.
                            '|correspondence-address|correspondence-email|correspondence-telephone',
                    ],
                    'defaults' => [
                        'controller' => UpdateAePropertyController::class,
                        'action' => 'edit',
                    ],
                ],
            ],
            'authorised-examiner-edit-property-review' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/authorised-examiner/:id/:propertyName/review/:formUuid',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'propertyName' => 'registered-address|correspondence-address',
                    ],
                    'defaults' => [
                        'controller' => UpdateAePropertyController::class,
                        'action' => 'review',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            SearchController::class => SearchController::class,
        ],
        'factories' => [
            SiteController::class => SiteControllerFactory::class,
            AuthorisedExaminerControllerFactory::class => AuthorisedExaminerControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            DirectDebitService::class => DirectDebitServiceFactory::class,
            UpdateAePropertyProcessBuilder::class => UpdateAePropertyProcessBuilderFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'mot-test-log/formatter/vehicle-model-sub-row' => __DIR__.'/../view/organisation/mot-test-log/formatter/vehicle-model-sub-row.phtml',
        ],
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],
];
