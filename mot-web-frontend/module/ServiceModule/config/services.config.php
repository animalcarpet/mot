<?php

use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\ServiceModule\Factory\VehicleServiceFactory;
use Dvsa\Mot\Frontend\ServiceModule\Factory\ApiServicesConfigOptionsFactory;
use Dvsa\Mot\Frontend\ServiceModule\Model\ApiServicesConfigOptions;

return [
    'factories' => [
        VehicleService::class => VehicleServiceFactory::class,
        ApiServicesConfigOptions::class => ApiServicesConfigOptionsFactory::class
    ],
];
