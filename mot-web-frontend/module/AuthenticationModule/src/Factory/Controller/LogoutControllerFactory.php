<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller;

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for LogoutController instances.
 */
class LogoutControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LogoutController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var WebLogoutService $logoutService */
        $logoutService = $serviceLocator->get(WebLogoutService::class);

        return new LogoutController($logoutService);
    }
}
