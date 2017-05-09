<?php

namespace Event\Factory\Controllers;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Event\Controller\EventController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EventController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $controllerManager = $serviceLocator->getServiceLocator();

        /** @var ContextProvider $contextProvider */
        $contextProvider = $controllerManager->get(ContextProvider::class);

        return new EventController($contextProvider);
    }
}
