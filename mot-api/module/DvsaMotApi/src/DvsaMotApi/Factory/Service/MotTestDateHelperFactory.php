<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestStatusService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestDateHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var MotTestRepository $motRepository */
        $motRepository = $entityManager->getRepository(MotTest::class);

        /** @var MotTestStatusService $motTestStatusService */
        $motTestStatusService = $serviceLocator->get('MotTestStatusService');

        return new MotTestDateHelperService(
            new DateTimeHolder(),
            $motRepository,
            $motTestStatusService
        );
    }
}
