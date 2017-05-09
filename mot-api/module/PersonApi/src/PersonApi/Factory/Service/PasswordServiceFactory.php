<?php

namespace PersonApi\Factory\Service;

use PersonApi\Service\PasswordService;
use PersonApi\Service\Validator\ChangePasswordValidator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use AccountApi\Service\OpenAmIdentityService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ClaimServiceFactory.
 */
class PasswordServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PasswordService(
            $serviceLocator->get(ChangePasswordValidator::class),
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $serviceLocator->get(OpenAmIdentityService::class)
        );
    }
}
