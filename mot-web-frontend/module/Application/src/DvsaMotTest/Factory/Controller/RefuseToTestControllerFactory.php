<?php

namespace DvsaMotTest\Factory\Controller;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Controller\RefuseToTestController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create RefuseToTestController.
 */
class RefuseToTestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SpecialNoticesController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);

        return new RefuseToTestController($paramObfuscator);
    }
}
