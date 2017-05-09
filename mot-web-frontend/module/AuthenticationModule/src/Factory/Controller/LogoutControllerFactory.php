<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller;

use Core\Service\MotEventManager;
use Core\Service\SessionService;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaClient\MapperFactory;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Factory for LogoutController instances.
 */
class LogoutControllerFactory implements FactoryInterface
{
    const TOKEN_SESSION_NAME = 'tokenSession';

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

        /**
         * @var MapperFactory
         */
        $mapper = $serviceLocator->get(MapperFactory::class);

        /** @var SessionService $sessionService */
        $sessionService = new SessionService(
            (new Container(SessionService::UNIQUE_KEY)), $mapper
        );

        /** @var EventManager $eventManager */
        $eventManager = $serviceLocator->get(MotEventManager::class);

        return new LogoutController($eventManager, $sessionService, $logoutService);
    }
}
