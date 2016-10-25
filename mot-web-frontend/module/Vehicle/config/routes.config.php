<?php

use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use Vehicle\Controller\MaskVehicleController;
use Vehicle\Controller\UnmaskVehicleController;
use Vehicle\Controller\VehicleController;
use Vehicle\UpdateVehicleProperty\Controller\UpdateVehiclePropertyController;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\ReviewMakeAndModelStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateMakeStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateModelStep;

return [
    'vehicle' => [
        'type' => 'segment',
        'options' => [
            'route' => '/vehicle',
            'defaults' => [
                'controller' => VehicleController::class,
                'action' => 'search',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'detail' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:id',
                    'constraints' => [
                        // 'vehicleId' => '[0-9]+',    // legacy mode: vehicleIds are integers
                        'vehicleId' => '[0-9a-zA-Z-_]+', // new mode: vehicleIds are obfuscated
                    ],
                    'defaults' => [
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'history' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/history',
                            'defaults' => [
                                'controller' => EnforcementMotTestSearchController::class,
                                'action'     => 'motTestSearchByVehicle',
                            ],
                        ],
                    ],
                    'change' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'engine' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/engine',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editEngine',
                                    ],
                                ],
                            ],
                            'country-of-registration' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/country-of-registration',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editCountry',
                                    ],
                                ],
                            ],
                            'class' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/class',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editClass',
                                    ],
                                ],
                            ],
                            'first-used-date' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/first-used-date',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editFirstUsedDate',
                                    ],
                                ],
                            ],
                            'make-and-model' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:property',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editMakeAndModel',
                                    ],
                                    'constraints' => [
                                        "property" => implode("|", [UpdateMakeStep::NAME, UpdateModelStep::NAME, ReviewMakeAndModelStep::NAME]),
                                    ],
                                ],
                            ],
                            'colour' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/colour',
                                    'defaults' => [
                                        'controller' => UpdateVehiclePropertyController::class,
                                        'action' => 'editColour',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'mask' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/enforcement/mask',
                            'defaults' => [
                                'controller' => MaskVehicleController::class,
                                'action'     => 'mask',
                            ],
                        ],
                    ],
                    'masked-successfully' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/enforcement/masked-successfully',
                            'defaults' => [
                                'controller' => MaskVehicleController::class,
                                'action'     => 'maskedSuccessfully',
                            ],
                        ],
                    ],
                    'unmask' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/enforcement/unmask',
                            'defaults' => [
                                'controller' => UnmaskVehicleController::class,
                                'action'     => 'unmask',
                            ],
                        ],
                    ],
                    'unmasked-successfully' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/enforcement/unmasked-successfully',
                            'defaults' => [
                                'controller' => UnmaskVehicleController::class,
                                'action'     => 'unmaskedSuccessfully',
                            ],
                        ],
                    ],
                ],
            ],
            'search' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/search',
                    'defaults' => [
                        'action' => 'search',
                    ],
                ],
            ],
            'result' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/result',
                    'defaults' => [
                        'action' => 'result',
                    ],
                ],
            ],
        ],
    ],
];
