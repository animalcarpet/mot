<?php

use Core\Catalog\EnumCatalog;
use Core\Factory\EnumCatalogFactory;
use Core\Factory\MotIdentityProviderFactory;
use Core\Factory\LazyMotFrontendAuthorisationServiceFactory;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Core\Factory\UrlHelperFactory;
use Core\Factory\WebPerformMotTestAssertionFactory;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use Core\Factory\WebAcknowledgeSpecialNoticeAssertionFactory;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Configuration\MotConfigFactory;
use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\CreateVehicleFormWizard;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\Factory\CreateVehicleFormWizardFactory;
use Zend\View\Helper\Url;

return [
    'factories' => [
        'MotIdentityProvider' => MotIdentityProviderFactory::class,
        'AuthorisationService' => LazyMotFrontendAuthorisationServiceFactory::class,
        MotAuthorisationServiceInterface::class => LazyMotFrontendAuthorisationServiceFactory::class,
        WebPerformMotTestAssertion::class => WebPerformMotTestAssertionFactory::class,
        WebAcknowledgeSpecialNoticeAssertion::class => WebAcknowledgeSpecialNoticeAssertionFactory::class,
        CreateVehicleFormWizard::class => CreateVehicleFormWizardFactory::class,
        MotConfig::class => MotConfigFactory::class,
        EnumCatalog::class => EnumCatalogFactory::class,
        Url::class => UrlHelperFactory::class,
    ],
    'abstract_factories' => [
        AutoWireFactory::class,
    ],
];