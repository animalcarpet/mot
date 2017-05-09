<?php

namespace PersonApi\Factory\Service;

use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Entity\PasswordDetail;
use PersonApi\Service\PasswordExpiryNotificationService;
use PersonApi\Service\PasswordExpiryService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;

class PasswordExpiryServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new PasswordExpiryService(
            $serviceLocator->get(PasswordExpiryNotificationService::class),
            $entityManager->getRepository(PasswordDetail::class),
            $serviceLocator->get(MotConfig::class),
            $serviceLocator->get(MotIdentityProviderInterface::class)
        );
    }
}
