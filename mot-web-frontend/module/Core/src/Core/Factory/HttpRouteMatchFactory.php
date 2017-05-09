<?php

namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Router\Http\RouteMatch;

class HttpRouteMatchFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RouteMatch
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $serviceLocator->get('Application')->getMvcEvent()->getRouteMatch();

        return $routeMatch;
    }
}
