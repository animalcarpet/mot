<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;

return [
    ContextProvider::YOUR_PROFILE_PARENT_ROUTE => [
        'type' => 'segment',
        'options' => [
            'route' => '/your-profile',
        ],
        'may_terminate' => true,
        'child_routes' => [
            'newProfileEmail' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/email',
                ],
                'may-terminate' => true,
            ],
            'driving-licence-change' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/driving-licence',
                    ],
                'may_terminate' => true,
                'child_routes' => [
                    'summary' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/summary',
                        ],
                    ],
                    'delete' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/delete',
                        ],
                    ],
                ],
            ],
            'trade-roles' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/trade-roles',
                ],
            ],
            'remove-ae-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-ae-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'remove-vts-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-vts-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'change-password' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-password',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
                        ],
                    ],
                ],

            ],
            'security-questions' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/security-question[/:questionNumber]',
                    'constraints' => [
                        'questionNumber' => '1|2',
                    ],
                ],
            ],
            'username-recover' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/username-recover',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ok' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ok',
                        ],
                    ],
                ],
            ],
            'change-qualification-status' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-qualification-status/:vehicleClassGroup',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
    ContextProvider::USER_SEARCH_PARENT_ROUTE => [
        'type' => 'segment',
        'options' => [
            'route' => '/user-admin/user/[:id]',
        ],
        'may_terminate' => true,
        'child_routes' => [
            'userAdminEmail' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/email',
                ],
                'may-terminate' => true,
            ],
            'driving-licence-change' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/driving-licence',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'summary' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/summary',
                        ],
                    ],
                    'delete' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/delete',
                        ],
                    ],
                ],
            ],
            'trade-roles' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/trade-roles',
                ],
            ],
            'remove-ae-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-ae-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'remove-vts-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-vts-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'manage-user-internal-role' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/manage-internal-role',
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
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'change-password' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-password',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
                        ],
                    ],
                ],

            ],
            'username-recover' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/username-recover',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ok' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ok',
                        ],
                    ],
                ],
            ],
            'change-qualification-status' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-qualification-status/:vehicleClassGroup',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'claim-reset' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/claim-reset',
                ],
                'may_terminate' => true,
            ],
            'claim-reset-by-post' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/claim-reset/post',
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
                ],
            ],
            'password-reset' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/password-reset',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ok' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ok',
                        ],
                    ],
                    'nok' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nok',
                        ],
                    ],
                ],
            ],
        ],
    ],
    ContextProvider::VTS_PARENT_ROUTE => [
        'type' => 'segment',
        'options' => [
            'route' => '/vehicle-testing-station/[:vehicleTestingStationId]/user/[:id]',
        ],
        'may_terminate' => true,
        'child_routes' => [
            'VTSEmail' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/email',
                ],
                'may-terminate' => true,
            ],
            'driving-licence-change' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/driving-licence',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'summary' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/summary',
                        ],
                    ],
                    'delete' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/delete',
                        ],
                    ],
                ],
            ],
            'trade-roles' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/trade-roles',
                ],
            ],
            'remove-ae-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-ae-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'remove-vts-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-vts-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'change-password' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-password',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
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
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ok' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ok',
                        ],
                    ],
                ],
            ],
            'change-qualification-status' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-qualification-status/:vehicleClassGroup',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
    ContextProvider::AE_PARENT_ROUTE => [
        'type' => 'segment',
        'options' => [
            'route' => '/authorised-examiner/[:authorisedExaminerId]/user/[:id]',
        ],
        'may_terminate' => true,
        'child_routes' => [
            'AEEmail' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/email',
                ],
                'may-terminate' => true,
            ],
            'driving-licence-change' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/driving-licence',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'summary' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/summary',
                        ],
                    ],
                    'delete' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/delete',
                        ],
                    ],
                ],
            ],
            'trade-roles' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/trade-roles',
                ],
            ],
            'remove-ae-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-ae-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'remove-vts-role' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/remove-vts-role/:entityId/:positionId',
                    'constraints' => [
                        'entityId' => '[0-9]+',
                        'positionId' => '[0-9]+',
                    ],
                ],
            ],
            'change-password' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-password',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
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
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ok' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ok',
                        ],
                    ],
                ],
            ],
            'change-qualification-status' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-qualification-status/:vehicleClassGroup',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'confirmation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/confirmation',
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
    'authorised-examiner' => [
        'type' => 'segment',
        'options' => [
            'route' => '/authorised-examiner[/:id]',
            'constraints' => [
                'id' => '[1-9]+[0-9]*',
            ],
        ],
        'may_terminate' => true,
    ],
    'vehicle-testing-station' => [
        'type' => 'segment',
        'options' => [
            'route' => '/vehicle-testing-station[/:id]',
            'constraints' => [
                'id' => '[1-9]+[0-9]*',
            ],
        ],
        'may_terminate' => true,
    ],
    'user_admin' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/user-admin',
            'defaults' => [
                'action' => 'index',
            ],
        ],
        'child_routes' => [
            'user-search' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/search',
                ],
                'may_terminate' => true,
            ],
        ],
    ],
];
