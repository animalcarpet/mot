<?php

namespace TestSupport\Factory;

use TestSupport\Service\TwoFactorAuthCardService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;

class TwoFactorAuthCardServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TwoFactorAuthCardService(
            $serviceLocator->get(EntityManager::class)
        );
    }
}
