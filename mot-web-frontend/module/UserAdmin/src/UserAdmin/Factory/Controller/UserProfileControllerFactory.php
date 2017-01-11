<?php

namespace UserAdmin\Factory\Controller;

use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Configuration\MotConfig;
use UserAdmin\Controller\UserProfileController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for {@link \UserAdmin\Controller\UserProfileController}
 */
class UserProfileControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $appServiceLocator = $controllerManager->getServiceLocator();

        $authorisationService = $appServiceLocator->get("AuthorisationService");
        $accountAdminService = $appServiceLocator->get(HelpdeskAccountAdminService::class);
        $testerGroupAuthorisationMapper = $appServiceLocator->get(TesterGroupAuthorisationMapper::class);
        $personRoleManagementService = $appServiceLocator->get(PersonRoleManagementService::class);
        $catalogService = $appServiceLocator->get("CatalogService");
        $config = $appServiceLocator->get(MotConfig::class);

        $config = $appServiceLocator->get(MotConfig::class);

        $controller = new UserProfileController(
            $authorisationService,
            $accountAdminService,
            $testerGroupAuthorisationMapper,
            $personRoleManagementService,
            $catalogService,
            $config
        );

        return $controller;
    }
}
