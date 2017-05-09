<?php

use Application\Factory\AuthAdapterFactory;
use Application\Factory\ZendAuthenticationServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Listener\WebAuthenticationListenerFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\AuthenticationAccountLockoutViewModelBuilderFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\GotoUrlServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\GotoUrlValidatorServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\IdentitySessionStateServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\LoginCsrfCookieServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\SuccessLoginResultRoutingServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\WebAuthenticationCookieServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\WebLoginServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\WebLogoutServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Listener\WebAuthenticationListener;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlValidatorService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\SuccessLoginResultRoutingService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaFeature\FeatureToggles;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Cache\Service\StorageCacheAbstractServiceFactory;
use Zend\Log\LoggerAbstractServiceFactory;

return [
    'factories' => [
        'AuthAdapter' => AuthAdapterFactory::class,
        AuthenticationAccountLockoutViewModelBuilder::class => AuthenticationAccountLockoutViewModelBuilderFactory::class,
        'ZendAuthenticationService' => ZendAuthenticationServiceFactory::class,
        'tokenService' => WebAuthenticationCookieServiceFactory::class,
        WebAuthenticationListener::class => WebAuthenticationListenerFactory::class,
        WebLogoutService::class => WebLogoutServiceFactory::class,
        GotoUrlService::class => GotoUrlServiceFactory::class,
        GotoUrlValidatorService::class => GotoUrlValidatorServiceFactory::class,
        IdentitySessionStateService::class => IdentitySessionStateServiceFactory::class,
        LoginCsrfCookieService::class => LoginCsrfCookieServiceFactory::class,
        WebLoginService::class => WebLoginServiceFactory::class,
        SuccessLoginResultRoutingService::class => SuccessLoginResultRoutingServiceFactory::class,
    ],
    'abstract_factories' => [
        StorageCacheAbstractServiceFactory::class,
        LoggerAbstractServiceFactory::class,
    ],
    'aliases' => [
        'translator' => 'MvcTranslator',
        ZendAuthenticationService::class => 'ZendAuthenticationService',
        FeatureToggles::class => 'Feature\FeatureToggles',
    ],
];
