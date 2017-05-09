<?php

use DvsaAuthentication\Authentication\Adapter\OpenAM\Factory\OpenAMApiTokenBasedAdapterFactory;
use DvsaAuthentication\Authentication\Adapter\OpenAM\Factory\OpenAMCachedClientFactory;
use DvsaAuthentication\Authentication\Adapter\OpenAM\OpenAMApiTokenBasedAdapter;
use DvsaAuthentication\Authentication\Adapter\OpenAM\OpenAMCachedClient;
use DvsaAuthentication\Authentication\Listener\AuthenticationListenerFactory;
use DvsaAuthentication\Factory\ApiTokenServiceFactory;
use DvsaAuthentication\Factory\IdentityFactoryFactory;
use DvsaAuthentication\Factory\OtpServiceFactory;
use DvsaAuthentication\Identity\Factory\IdentityByTokenResolverFactory;
use DvsaAuthentication\Identity\IdentityByTokenResolver;
use DvsaAuthentication\Identity\OpenAM\OpenAMIdentityByTokenResolver;
use DvsaAuthentication\Login\Factory\AuthenticatorFactory;
use DvsaAuthentication\Login\Factory\LoginServiceFactory;
use DvsaAuthentication\Login\LoginService;
use DvsaAuthentication\Login\UsernamePasswordAuthenticator;
use DvsaAuthentication\Service\OtpService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaAuthentication\Factory\Service\TwoFactorStatusServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            'DvsaAuthenticationService' => \DvsaAuthentication\Factory\AuthenticationServiceFactory::class,
            AuthenticationListenerFactory::class => AuthenticationListenerFactory::class,
            MotIdentityProviderInterface::class => \DvsaAuthentication\Factory\IdentityProviderFactory::class,
            'MotIdentityProvider' => \DvsaAuthentication\Factory\IdentityProviderFactory::class,
            'tokenService' => ApiTokenServiceFactory::class,
            OpenAMApiTokenBasedAdapter::class => OpenAMApiTokenBasedAdapterFactory::class,
            OtpService::class => OtpServiceFactory::class,
            OpenAMCachedClient::class => OpenAMCachedClientFactory::class,
            IdentityFactoryFactory::class => IdentityFactoryFactory::class,
            UsernamePasswordAuthenticator::class => AuthenticatorFactory::class,
            LoginService::class => LoginServiceFactory::class,
            IdentityByTokenResolver::class => IdentityByTokenResolverFactory::class,
            OpenAMIdentityByTokenResolver::class => IdentityByTokenResolverFactory::class,
            IdentityAttributesMapper::class => IdentityAttributesMapperFactory::class,
            TwoFactorStatusService::class => TwoFactorStatusServiceFactory::class,
        ],
    ],
];
