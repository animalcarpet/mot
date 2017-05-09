<?php

use DvsaMotApi\Controller\MotTestController;
use DvsaMotApi\Controller\VehicleHistoryController;
use VehicleApi\Controller\MysteryShopperVehicleController;
use VehicleApi\Controller\VehicleCertificateExpiryController;
use VehicleApi\Controller\VehicleController;
use VehicleApi\Controller\VehicleDvlaController;
use VehicleApi\Controller\VehicleRetestEligibilityController;
use VehicleApi\Controller\VehicleSearchController;
use VehicleApi\Factory\Controller\MysteryShopperVehicleControllerFactory;
use VehicleApi\Factory\Controller\VehicleControllerFactory;
use VehicleApi\Factory\Controller\VehicleRetestEligibilityControllerFactory;
use VehicleApi\Factory\Controller\VehicleSearchControllerFactory;

$config = [
    'invokables' => [
        VehicleCertificateExpiryController::class => VehicleCertificateExpiryController::class,
        VehicleHistoryController::class => VehicleHistoryController::class,
        VehicleDvlaController::class => VehicleDvlaController::class,
        MotTestController::class => MotTestController::class,
    ],
    'factories' => [
        MysteryShopperVehicleController::class => MysteryShopperVehicleControllerFactory::class,
        VehicleRetestEligibilityController::class => VehicleRetestEligibilityControllerFactory::class,
        VehicleSearchController::class => VehicleSearchControllerFactory::class,
        VehicleController::class => VehicleControllerFactory::class,
    ],
];

return $config;
