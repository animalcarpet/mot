<?php

namespace SiteApi\Factory\Controller;

use Doctrine\ORM\EntityManager;
use SiteApi\Controller\MotTestLogController;
use SiteApi\Service\MotTestLogService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestLogControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        $elasticSearchService = $serviceLocator->get('ElasticSearchService');
        $entityManager = $serviceLocator->get(EntityManager::class);
        $motTestLogService = $serviceLocator->get(MotTestLogService::class);

        return new MotTestLogController($motTestLogService, $elasticSearchService, $entityManager);
    }
}
