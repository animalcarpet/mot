<?php

use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use Vehicle\Controller\MaskVehicleController;
use Vehicle\Controller\UnmaskVehicleController;
use Vehicle\Controller\VehicleController;
use Vehicle\TestingAdvice\Controller\AdviceController;
use Vehicle\CreateVehicle\Controller\ClassController;
use Vehicle\CreateVehicle\Controller\ColourController;
use Vehicle\CreateVehicle\Controller\ConfirmationController;
use Vehicle\CreateVehicle\Controller\CountryOfRegistrationController;
use Vehicle\CreateVehicle\Controller\DateOfFirstUseController;
use Vehicle\CreateVehicle\Controller\EngineController;
use Vehicle\CreateVehicle\Controller\MakeController;
use Vehicle\CreateVehicle\Controller\ModelController;
use Vehicle\CreateVehicle\Controller\RegistrationAndVinController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Controller\StartController;
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
                    'change-under-test' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/change-under-test',
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
                    'testing-advice' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/testing-advice',
                            'defaults' => [
                                'controller' => AdviceController::class,
                                'action'     => 'display',
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
    'create-vehicle' => [
        'type' => 'segment',
        'options' => [
            'route' => '/create-vehicle',
            'defaults' => [
                'controller' => StartController::class,
                'action' => 'index',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'new-vehicle-vrm-and-vin' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/vrm-and-vin',
                    'defaults' => [
                        'controller' => RegistrationAndVinController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-make' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/make',
                    'defaults' => [
                        'controller' => MakeController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'new-vehicle-model' => [
                'type' => 'segment',
                'options' => [
                    'route'     => '/model',
                    'defaults' => [
                        'controller' => ModelController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'new-vehicle-engine' => [
                'type' => 'segment',
                'options' => [
                    'route'     => '/engine',
                    'defaults' => [
                        'controller' => EngineController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-class' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/class',
                    'defaults' => [
                        'controller' => ClassController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-colour' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/colour',
                    'defaults' => [
                        'controller' => ColourController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-country-of-reg' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/country-of-registration',
                    'defaults' => [
                        'controller' => CountryOfRegistrationController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-first-use-date' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/first-use-date',
                    'defaults' => [
                        'controller' => DateOfFirstUseController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-review' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/review',
                    'defaults' => [
                        'controller' => ReviewController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'new-vehicle-created-and-started' => [
                'type' => 'segment',
                'options' => [
                    'route'    => '/created-and-started',
                    'defaults' => [
                        'controller' => ConfirmationController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
];
