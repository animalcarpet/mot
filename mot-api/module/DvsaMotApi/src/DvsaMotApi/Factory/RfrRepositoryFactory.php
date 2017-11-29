<?php

namespace DvsaMotApi\Factory;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Date\RfrCurrentDateFaker;
use DvsaEntities\Repository\RfrRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RfrRepositoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RfrRepository(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(RfrCurrentDateFaker::class),
            $serviceLocator->get(MotConfig::class)->withDefault([])->get('disabled_rfrs')
        );
    }
}
