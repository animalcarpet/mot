<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\MotTest;
use SiteApi\Service\MotTestInProgressService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MotTestInProgressServiceFactory.
 */
class MotTestInProgressServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MotTestInProgressService(
            $serviceLocator->get(EntityManager::class)->getRepository(MotTest::class),
            $serviceLocator->get('DvsaAuthorisationService')
        );
    }
}
