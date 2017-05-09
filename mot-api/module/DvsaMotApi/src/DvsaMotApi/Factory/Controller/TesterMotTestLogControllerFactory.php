<?php

namespace DvsaMotApi\Factory\Controller;

use Doctrine\ORM\EntityManager;
use DvsaMotApi\Controller\TesterMotTestLogController;
use DvsaMotApi\Service\TesterMotTestLogService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TesterMotTestLogControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /**
         * @var ServiceLocatorInterface
         */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new TesterMotTestLogController(
            $serviceLocator->get(TesterMotTestLogService::class),
            $serviceLocator->get('ElasticSearchService'),
            $serviceLocator->get(EntityManager::class)
        );
    }
}
