<?php

use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Data\ApiNotificationResource;
use Dashboard\Factory\Authorisation\ViewTradeRolesAssertionFactory;
use Dashboard\Factory\Security\DashboardGuardFactory;
use Dashboard\Factory\Service\PersonTradeRoleSorterServiceFactory;
use Dashboard\PersonStore;
use Dashboard\Security\DashboardGuard;
use Dashboard\Service\PasswordService;
use Dashboard\Service\PersonTradeRoleSorterService;
use Dashboard\Service\TradeRolesAssociationsService;
use Dashboard\Factory\Service\TradeRolesAssociationsServiceFactory;
use DvsaCommon\Factory\AutoWire\AutoWireFactory;

return [
    'factories' => [
        PersonStore::class => \Dashboard\Factory\PersonStoreFactory::class,
        ApiNotificationResource::class => \Dashboard\Factory\ApiNotificationResourceFactory::class,
        ApiDashboardResource::class => \Dashboard\Factory\ApiDashboardResourceFactory::class,
        PasswordService::class => \Dashboard\Factory\Service\PasswordServiceFactory::class,
        ViewTradeRolesAssertion::class => ViewTradeRolesAssertionFactory::class,
        TradeRolesAssociationsService::class => TradeRolesAssociationsServiceFactory::class,
        PersonTradeRoleSorterService::class => PersonTradeRoleSorterServiceFactory::class,
        DashboardGuard::class => DashboardGuardFactory::class,
    ],
    'abstract_factories' => [
        AutoWireFactory::class,
    ],
];
