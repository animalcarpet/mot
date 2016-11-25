<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaMotApi\Helper\MysteryShopperHelper;
use PersonApi\Service\UserStatsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEntities\Repository\MotTestRepository;

class UserStatsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $entityManager->getRepository(\DvsaEntities\Entity\MotTest::class);
        /** @var MysteryShopperHelper $mysteryShopperHelper */
        $mysteryShopperHelper = $serviceLocator->get(MysteryShopperHelper::class);
        return new UserStatsService($entityManager, $motTestRepository, $mysteryShopperHelper);
    }
}
