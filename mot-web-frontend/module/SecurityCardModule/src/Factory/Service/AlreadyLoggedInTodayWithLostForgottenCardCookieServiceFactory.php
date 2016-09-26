<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Factory\Service;

use Dvsa\OpenAM\Options\OpenAMClientOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Core\Service\MotFrontendIdentityProvider;

class AlreadyLoggedInTodayWithLostForgottenCardCookieServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AlreadyLoggedInTodayWithLostForgottenCardCookieService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $identityProvider MotFrontendIdentityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new AlreadyLoggedInTodayWithLostForgottenCardCookieService($identityProvider);
    }
}