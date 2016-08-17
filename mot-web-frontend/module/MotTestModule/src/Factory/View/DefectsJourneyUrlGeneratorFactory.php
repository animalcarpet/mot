<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Zend\Http\PhpEnvironment\Request;

class DefectsJourneyUrlGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RouteStackInterface $router */
        $router = $serviceLocator->get('Router');

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        /** @var DefectsJourneyContextProvider $contextProvider */
        $contextProvider = $serviceLocator->get(DefectsJourneyContextProvider::class);

        return new DefectsJourneyUrlGenerator(
            $router,
            $request,
            $contextProvider
        );
    }
}