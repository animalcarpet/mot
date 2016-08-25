<?php

use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Api\ServiceModule\Factory\VehicleServiceFactory;
use Dvsa\Mot\Api\ServiceModule\Factory\ApiServicesConfigOptionsFactory;
use Dvsa\Mot\Api\ServiceModule\Model\ApiServicesConfigOptions;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Api\ServiceModule\Factory\AuthorisationServiceFactory;

return [
    'factories' => [
        VehicleService::class => VehicleServiceFactory::class,
        ApiServicesConfigOptions::class => ApiServicesConfigOptionsFactory::class,
        AuthorisationService::class => AuthorisationServiceFactory::class,
    ],
];
