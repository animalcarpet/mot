<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Factory\Service;

use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SecurityCardServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SecurityCardService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $authorisationService AuthorisationService */
        $authorisationService = $serviceLocator->get(AuthorisationService::class);

        /** @var TwoFactorNominationNotificationService $nominationService */
        $nominationService = $serviceLocator->get(TwoFactorNominationNotificationService::class);

        return new SecurityCardService($authorisationService, $nominationService);
    }
}
