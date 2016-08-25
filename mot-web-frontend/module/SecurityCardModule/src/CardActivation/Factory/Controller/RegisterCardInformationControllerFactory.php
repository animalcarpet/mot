<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use DvsaCommon\HttpRestJson\Client;
use Core\Service\LazyMotFrontendAuthorisationService;

/**
 * Factory for SecurityController instances.
 */
class RegisterCardInformationControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();
        $cookieService = $serviceLocator->get(RegisterCardInformationCookieService::class);
        $request = $serviceLocator->get('Request');
        $response = $serviceLocator->get('Response');
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);

        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new RegisterCardInformationController(
            $cookieService, $request, $response, $identityProvider, $authorisationService, $twoFaFeatureToggle);
    }
}