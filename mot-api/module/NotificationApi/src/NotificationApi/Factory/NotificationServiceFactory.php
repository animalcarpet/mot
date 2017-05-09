<?php

namespace NotificationApi\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\Validator\NotificationValidator;
use DvsaEntities\Entity\Notification;
use Doctrine\ORM\EntityManager;

/**
 * Class NotificationServiceFactory.
 */
class NotificationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new NotificationService(
            $serviceLocator,
            new NotificationValidator(),
            $entityManager->getRepository(Notification::class)
        );
    }
}
