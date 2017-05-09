<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaMotApi\Controller\TesterController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for the TesterController.
 */
class TesterControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return TesterController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $testerService = $serviceLocator->get('TesterService');

        return new TesterController($testerService);
    }
}
