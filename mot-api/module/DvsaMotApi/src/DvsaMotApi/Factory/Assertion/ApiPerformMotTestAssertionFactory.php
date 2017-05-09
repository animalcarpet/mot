<?php

namespace DvsaMotApi\Factory\Assertion;

use DvsaCommon\Auth\Assertion\PerformMotTestAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApiPerformMotTestAssertionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApiPerformMotTestAssertion(
            new PerformMotTestAssertion(
                $serviceLocator->get('DvsaAuthorisationService'),
                $serviceLocator->get(MotIdentityProviderInterface::class)
            )
        );
    }
}
