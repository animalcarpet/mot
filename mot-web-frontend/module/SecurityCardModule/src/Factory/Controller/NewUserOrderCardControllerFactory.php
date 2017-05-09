<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommon\Auth\MotIdentityProviderInterface;

class NewUserOrderCardControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        /** @var Response $response */
        $response = $serviceLocator->get('Response');

        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        /** @var Client $client */
        $client = $serviceLocator->get(Client::class);

        /** @var LazyMotFrontendAuthorisationService $authorisationService */
        $authorisationService = new LazyMotFrontendAuthorisationService(
            $identityProvider,
            $client
        );

        /** @var TwoFaFeatureToggle $twoFaFeatureToggle */
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new NewUserOrderCardController(
            $request, $response, $identityProvider, $authorisationService, $twoFaFeatureToggle);
    }
}
