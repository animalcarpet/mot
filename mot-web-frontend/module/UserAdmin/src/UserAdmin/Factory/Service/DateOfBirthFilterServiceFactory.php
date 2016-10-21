<?php

namespace UserAdmin\Factory\Service;

use Application\Data\ApiPersonalDetails;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use UserAdmin\Service\DateOfBirthFilterService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating DateOfBirthFilterService instances.
 */
class DateOfBirthFilterServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DateOfBirthFilterService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get('AuthorisationService');

        /** @var ApiPersonalDetails $personalDetailsService */
        $personalDetailsService = $serviceLocator->get(ApiPersonalDetails::class);

        return new DateOfBirthFilterService($authorisationService, $personalDetailsService);
    }
}