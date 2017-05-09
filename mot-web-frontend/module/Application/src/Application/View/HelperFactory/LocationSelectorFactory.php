<?php

namespace Application\View\HelperFactory;

use Application\View\Helper\LocationSelector;
use DvsaMotTest\Helper\LocationSelectContainerHelper;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class LocationSelectorFactory.
 */
class LocationSelectorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();
        /** @var LocationSelectContainerHelper $container */
        $container = $sl->get('LocationSelectContainerHelper');
        /** @var RouteMatch $routeMatch */
        $routeMatch = $sl->get('Application')->getMvcEvent()->getRouteMatch();
        $helper = new LocationSelector($container, $routeMatch);

        return $helper;
    }
}
