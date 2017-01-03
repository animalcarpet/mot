<?php

namespace DvsaMotTest\Factory\Controller;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Controller\StartTestConfirmationController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create StartTestConfirmationController.
 */
class StartTestConfirmationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SpecialNoticesController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator     = $controllerManager->getServiceLocator();
        $paramObfuscator    = $serviceLocator->get(ParamObfuscator::class);

        return new StartTestConfirmationController($paramObfuscator);
    }
}
