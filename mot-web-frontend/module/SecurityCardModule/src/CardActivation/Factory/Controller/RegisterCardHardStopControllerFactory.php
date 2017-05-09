<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardHardStopAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardHardStopController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegisterCardHardStopControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $hardStopAction = $serviceLocator->get(RegisterCardHardStopAction::class);

        return new RegisterCardHardStopController($hardStopAction);
    }
}
