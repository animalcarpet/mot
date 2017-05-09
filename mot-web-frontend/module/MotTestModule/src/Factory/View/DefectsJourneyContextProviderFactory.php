<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DefectJourneyContextProviderFactory.
 */
class DefectsJourneyContextProviderFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DefectsJourneyContextProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var TreeRouteStack $router */
        $router = $serviceLocator->get('Router');

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        return new DefectsJourneyContextProvider($router, $request);
    }
}
