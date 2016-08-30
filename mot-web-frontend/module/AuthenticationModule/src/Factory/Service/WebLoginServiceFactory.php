<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service;

use Account\Service\ExpiredPasswordService;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\HttpRestJson\Client;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;

class WebLoginServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $serviceLocator->get('ZendAuthenticationService');

        /** @var  DtoReflectiveDeserializer $deserializer*/
        $deserializer = $serviceLocator->get(DtoReflectiveDeserializer::class);

        /** @var  Client $client */
        $client = $serviceLocator->get(Client::class);

        /** @var  WebAuthenticationCookieService $authenticationCookieService*/
        $authenticationCookieService = $serviceLocator->get('tokenService');

        /** @var  ExpiredPasswordService $expiredPasswordService*/
        $expiredPasswordService = $serviceLocator->get(ExpiredPasswordService::class);

        /** @var  LazyMotFrontendAuthorisationService $authorisationService*/
        $authorisationService = $serviceLocator->get('AuthorisationService');

        return new WebLoginService(
            $authenticationService,
            $client,
            $deserializer,
            new SessionManager(),
            $authenticationCookieService,
            $expiredPasswordService,
            $authorisationService
        );
    }
}
