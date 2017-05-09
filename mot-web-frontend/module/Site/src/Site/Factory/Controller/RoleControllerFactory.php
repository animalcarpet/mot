<?php

namespace Site\Factory\Controller;

use DvsaCommon\Validator\UsernameValidator;
use Site\Controller\RoleController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RoleControllerFactory.
 */
class RoleControllerFactory implements FactoryInterface
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
        $usernameValidator = $serviceLocator->get(UsernameValidator::class);
        $htmlPurifier = $serviceLocator->get('HTMLPurifier');

        return new RoleController($usernameValidator, $htmlPurifier);
    }
}
