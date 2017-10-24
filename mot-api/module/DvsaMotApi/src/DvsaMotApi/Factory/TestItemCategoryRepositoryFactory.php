<?php

namespace DvsaMotApi\Factory;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\RfrCurrentDateFaker;
use DvsaEntities\Repository\TestItemCategoryRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TestItemCategoryRepositoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TestItemCategoryRepository(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(RfrCurrentDateFaker::class)
        );
    }
}
