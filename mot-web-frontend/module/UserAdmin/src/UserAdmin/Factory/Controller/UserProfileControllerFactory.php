<?php

namespace UserAdmin\Factory\Controller;

use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use UserAdmin\Controller\UserProfileController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for {@link \UserAdmin\Controller\UserProfileController}.
 */
class UserProfileControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $appServiceLocator = $controllerManager->getServiceLocator();

        $authorisationService = $appServiceLocator->get('AuthorisationService');
        $accountAdminService = $appServiceLocator->get(HelpdeskAccountAdminService::class);
        $testerGroupAuthorisationMapper = $appServiceLocator->get(TesterGroupAuthorisationMapper::class);
        $personRoleManagementService = $appServiceLocator->get(PersonRoleManagementService::class);
        $catalogService = $appServiceLocator->get('CatalogService');
        $viewTradeRolesAssertion = $appServiceLocator->get(ViewTradeRolesAssertion::class);
        $registeredCardService = $appServiceLocator->get(RegisteredCardService::class);
        /** @var TwoFaFeatureToggle */
        $twoFaFeatureToggle = $appServiceLocator->get(TwoFaFeatureToggle::class);

        $controller = new UserProfileController(
            $authorisationService,
            $accountAdminService,
            $testerGroupAuthorisationMapper,
            $personRoleManagementService,
            $catalogService,
            $viewTradeRolesAssertion,
            $registeredCardService,
            $twoFaFeatureToggle
        );

        return $controller;
    }
}
