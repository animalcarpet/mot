<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaMotApi\Controller\NonMotInspectionController;
use DvsaMotApi\Service\MotTestService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NonMotInspectionControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return NonMotInspectionController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /* @var MotTestService $motTestService */
        $motTestService = $serviceLocator->get('MotTestService');
        /** @var AbstractMotAuthorisationService $authService */
        $authService = $serviceLocator->get('DvsaAuthorisationService');

        return new NonMotInspectionController(
            $motTestService,
            $authService
        );
    }
}
