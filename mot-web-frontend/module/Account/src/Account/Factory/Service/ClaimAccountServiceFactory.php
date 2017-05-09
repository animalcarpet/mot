<?php

namespace Account\Factory\Service;

use Account\Service\ClaimAccountService;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClaimAccountServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ClaimAccountService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotIdentityProviderInterface $container */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new ClaimAccountService(
            $serviceLocator->get('AuthorisationService'),
            $identityProvider->getIdentity(),
            $serviceLocator->get(MapperFactory::class),
            $serviceLocator->get(ParamObfuscator::class)
        );
    }
}
