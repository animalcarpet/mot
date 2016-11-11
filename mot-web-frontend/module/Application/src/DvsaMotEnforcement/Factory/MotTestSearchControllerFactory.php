<?php

namespace DvsaMotEnforcement\Factory;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotEnforcement\Controller\MotTestSearchController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create MotTestSearchController.
 */
class MotTestSearchControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return MotTestSearchController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);
        /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get('AuthorisationService');

        return new MotTestSearchController($paramObfuscator, $authorisationService);
    }
}
