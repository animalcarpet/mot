<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Repository\MotTestRepository;
use PersonApi\Service\UserStatsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserStatsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $entityManager->getRepository(\DvsaEntities\Entity\MotTest::class);

        return new UserStatsService($entityManager, $motTestRepository);
    }
}
