<?php

namespace UserAdmin\Factory\Controller;

use DvsaClient\MapperFactory;
use UserAdmin\Controller\RecordDemoTestController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RecordDemoTestControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $appServiceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var MapperFactory $mapperFactory */
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        $controller = new RecordDemoTestController(
            $serviceLocator->get('AuthorisationService'),
            $mapperFactory->DemoTestAssessment,
            $mapperFactory->Person
        );

        return $controller;
    }
}
