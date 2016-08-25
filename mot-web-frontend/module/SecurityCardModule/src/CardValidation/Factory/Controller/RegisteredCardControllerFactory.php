<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;

use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;

class RegisteredCardControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var RegisteredCardService $service */
        $service = $serviceLocator->get(RegisteredCardService::class);

        $authenticationService = $serviceLocator->get('ZendAuthenticationService');

        $request = $serviceLocator->get('Request');

        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new RegisteredCardController
        (
            $service,
            $authenticationService,
            $request,
            $twoFaFeatureToggle
        );
    }
}