<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Factory\Security;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SecurityCardGuardFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var SecurityCardService $securityCardService */
        $securityCardService = $serviceLocator->get(SecurityCardService::class);

        /** @var AuthorisationService $authorisationServiceClient */
        $authorisationServiceClient = $serviceLocator->get(AuthorisationService::class);

        /** @var PersonRoleManagementService $personRoleManagementService */
        $personRoleManagementService = $serviceLocator->get(PersonRoleManagementService::class);

        /** @var TwoFaFeatureToggle $twoFaFeatureToggle */
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get('AuthorisationService');

        return new SecurityCardGuard(
            $securityCardService,
            $authorisationServiceClient,
            $personRoleManagementService,
            $twoFaFeatureToggle,
            $authorisationService
        );
    }
}
