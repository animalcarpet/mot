<?php

namespace TestSupport\Factory;

use Doctrine\ORM\EntityManager;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Service\OrganisationRoleNominationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RoleNominationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OrganisationRoleNominationService(
            $serviceLocator->get(TestSupportRestClientHelper::class),
            $serviceLocator->get(EntityManager::class)
        );
    }
}
