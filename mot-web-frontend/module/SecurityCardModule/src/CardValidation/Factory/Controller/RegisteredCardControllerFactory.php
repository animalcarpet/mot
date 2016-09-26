<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;

class RegisteredCardControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var AlreadyLoggedInTodayWithLostForgottenCardCookieService $cookieService */
        $cookieService = $serviceLocator->get(AlreadyLoggedInTodayWithLostForgottenCardCookieService::class);

        /** @var RegisteredCardService $service */
        $service = $serviceLocator->get(RegisteredCardService::class);

        $authenticationService = $serviceLocator->get('ZendAuthenticationService');

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        /** @var Response $response */
        $response = $serviceLocator->get('Response');

        /** @var TwoFaFeatureToggle $twoFaFeatureToggle */
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new RegisteredCardController
        (
            $service,
            $authenticationService,
            $request,
            $response,
            $twoFaFeatureToggle,
            $cookieService,
            $identityProvider->getIdentity()
        );
    }
}