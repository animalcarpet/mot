<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaMotApi\Controller\DemoTestAssessmentController;
use DvsaMotApi\Service\DemoTestAssessmentService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DemoTestAssessmentControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $demoTestAssessmentService = $serviceLocator->get(DemoTestAssessmentService::class);

        return new DemoTestAssessmentController($demoTestAssessmentService);
    }
}
