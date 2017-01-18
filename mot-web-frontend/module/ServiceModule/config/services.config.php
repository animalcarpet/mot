<?php

use Dvsa\Mot\Frontend\ServiceModule\Factory\ApiServicesConfigOptionsFactory;
use Dvsa\Mot\Frontend\ServiceModule\Factory\MotTestServiceFactory;
use Dvsa\Mot\Frontend\ServiceModule\Factory\VehicleServiceFactory;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\ServiceModule\Factory\AuthorisationServiceFactory;
use Dvsa\Mot\Frontend\ServiceModule\Model\ApiServicesConfigOptions;

return [
    'factories' => [
        VehicleService::class => VehicleServiceFactory::class,
        AuthorisationService::class => AuthorisationServiceFactory::class,
        MotTestService::class => MotTestServiceFactory::class,
        ApiServicesConfigOptions::class => ApiServicesConfigOptionsFactory::class
    ],
];
