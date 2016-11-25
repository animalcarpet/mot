<?php

namespace DvsaMotTest\Factory\Controller;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;
use DvsaMotTest\Controller\TesterMotTestLogController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class TesterMotTestLogControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new TesterMotTestLogController(
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(MapperFactory::class),
            $serviceLocator->get('Feature\FeatureToggles'),
            $serviceLocator->get(ContextProvider::class)
        );
    }
}
