<?php

namespace CensorApi\Factory\Service;

use CensorApi\Service\CensorService;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\CensorBlacklist;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CensorServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        $censorBlacklistRepository = $entityManager->getRepository(CensorBlacklist::class);
        $censorService = new CensorService($censorBlacklistRepository);
        return $censorService;
    }
}
