<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaCommon\Validator\UsernameValidator;
use DvsaMotApi\Controller\UserController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class UserControllerFactory.
 */
class UserControllerFactory implements FactoryInterface
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

        return new UserController($usernameValidator);
    }
}
