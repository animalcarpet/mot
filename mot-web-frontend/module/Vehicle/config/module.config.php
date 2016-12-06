<?php

use DvsaMotEnforcement\Controller\MotTestSearchController as EnforcementMotTestSearchController;
use DvsaMotEnforcement\Factory\MotTestSearchControllerFactory as EnforcementMotTestSearchControllerFactory;
use Vehicle\CreateVehicle\Action\ClassAction;
use Vehicle\CreateVehicle\Action\ColourAction;
use Vehicle\CreateVehicle\Action\ConfirmationAction;
use Vehicle\CreateVehicle\Action\CountryOfRegistrationAction;
use Vehicle\CreateVehicle\Action\DateOfFirstUseAction;
use Vehicle\CreateVehicle\Action\EngineAction;
use Vehicle\CreateVehicle\Action\MakeAction;
use Vehicle\CreateVehicle\Action\ModelAction;
use Vehicle\CreateVehicle\Action\RegistrationAndVinAction;
use Vehicle\CreateVehicle\Action\ReviewAction;
use Vehicle\CreateVehicle\Action\StartAction;
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
use Vehicle\CreateVehicle\Factory\Action\ClassActionFactory;
use Vehicle\CreateVehicle\Factory\Action\ColourActionFactory;
use Vehicle\CreateVehicle\Factory\Action\ConfirmationActionFactory;
use Vehicle\CreateVehicle\Factory\Action\CountryOfRegistrationActionFactory;
use Vehicle\CreateVehicle\Factory\Action\DateOfFirstUseActionFactory;
use Vehicle\CreateVehicle\Factory\Action\EngineActionFactory;
use Vehicle\CreateVehicle\Factory\Action\MakeActionFactory;
use Vehicle\CreateVehicle\Factory\Action\ModelActionFactory;
use Vehicle\CreateVehicle\Factory\Action\RegistrationAndVinActionFactory;
use Vehicle\CreateVehicle\Factory\Action\ReviewActionFactory;
use Vehicle\CreateVehicle\Factory\Action\StartActionFactory;
use Vehicle\CreateVehicle\Factory\Controller\ClassControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\ColourControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\ConfirmationControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\CountryOfRegistrationControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\DateOfFirstUseControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\EngineControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\MakeControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\ModelControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\RegistrationAndVinControllerFactory;
use Vehicle\CreateVehicle\Factory\Controller\ReviewControllerFactory;
use Vehicle\CreateVehicle\Factory\Service\CreateNewVehicleServiceFactory;
use Vehicle\CreateVehicle\Factory\Service\CreateVehicleSessionServiceFactory;
use Vehicle\CreateVehicle\Factory\Service\CreateVehicleStepServiceFactory;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleSessionService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Vehicle\Factory\Service\VehicleCatalogServiceFactory;
use Vehicle\Service\VehicleCatalogService;
use Vehicle\CreateVehicle\Controller\StartController;
use Vehicle\CreateVehicle\Factory\Controller\StartControllerFactory;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Factory\Service\CreateVehicleModelServiceFactory;

return [
    'router'        => ['routes' => require __DIR__ . '/routes.config.php'],
    'controllers'   => [
        'invokables' => [
        ],
        'factories' => [
            EnforcementMotTestSearchController::class => EnforcementMotTestSearchControllerFactory::class,
            StartController::class => StartControllerFactory::class,
            EngineController::class => EngineControllerFactory::class,
            ClassController::class => ClassControllerFactory::class,
            ColourController::class => ColourControllerFactory::class,
            ConfirmationController::class => ConfirmationControllerFactory::class,
            CountryOfRegistrationController::class => CountryOfRegistrationControllerFactory::class,
            DateOfFirstUseController::class => DateOfFirstUseControllerFactory::class,
            MakeController::class => MakeControllerFactory::class,
            ModelController::class => ModelControllerFactory::class,
            ReviewController::class => ReviewControllerFactory::class,
            RegistrationAndVinController::class => RegistrationAndVinControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            VehicleCatalogService::class => VehicleCatalogServiceFactory::class,
            CreateVehicleSessionService::class => CreateVehicleSessionServiceFactory::class,
            CreateVehicleStepService::class => CreateVehicleStepServiceFactory::class,
            EngineAction::class => EngineActionFactory::class,
            ClassAction::class => ClassActionFactory::class,
            ColourAction::class => ColourActionFactory::class,
            ConfirmationAction::class => ConfirmationActionFactory::class,
            CountryOfRegistrationAction::class => CountryOfRegistrationActionFactory::class,
            DateOfFirstUseAction::class => DateOfFirstUseActionFactory::class,
            MakeAction::class => MakeActionFactory::class,
            ModelAction::class => ModelActionFactory::class,
            RegistrationAndVinAction::class => RegistrationAndVinActionFactory::class,
            ReviewAction::class => ReviewActionFactory::class,
            StartAction::class => StartActionFactory::class,
            CreateVehicleModelService::class => CreateVehicleModelServiceFactory::class,
            CreateNewVehicleService::class => CreateNewVehicleServiceFactory::class,
        ],
    ],
    'view_manager'  => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
