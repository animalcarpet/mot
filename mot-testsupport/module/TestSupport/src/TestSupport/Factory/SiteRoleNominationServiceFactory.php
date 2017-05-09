<?php

namespace TestSupport\Factory;

use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Service\SiteRoleNominationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteRoleNominationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SiteRoleNominationService(
            $serviceLocator->get(TestSupportRestClientHelper::class)
        );
    }
}
