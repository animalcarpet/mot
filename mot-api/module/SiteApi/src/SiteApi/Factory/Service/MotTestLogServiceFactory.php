<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\MotTest;
use SiteApi\Service\Mapper\MotTestLogSummaryMapper;
use SiteApi\Service\MotTestLogService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestLogServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dvsaAuthorisationService = $serviceLocator->get('DvsaAuthorisationService');
        $entityManager = $serviceLocator->get(EntityManager::class);
        $motTestRepository = $entityManager->getRepository(MotTest::class);
        $motTestLogSummaryMapper = new MotTestLogSummaryMapper();

        return new MotTestLogService($dvsaAuthorisationService, $motTestRepository, $motTestLogSummaryMapper);
    }
}
