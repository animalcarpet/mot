<?php

namespace DvsaMotApi\Factory\Assertion;

use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApiReadMotTestAssertionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ReadMotTestAssertion(
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('DvsaAuthenticationService')
        );
    }
}
