<?php

namespace UserAdmin\Factory\Controller;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use UserAdmin\Controller\DrivingLicenceController;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use UserAdmin\Service\HelpdeskAccountAdminService;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use UserAdmin\Service\UserAdminSessionService;

/**
 * Factory for {@link \UserAdmin\Controller\DrivingLicenceController}.
 */
class DrivingLicenceControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return DrivingLicenceController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $serviceLocator = $controllerManager->getServiceLocator();

        return new DrivingLicenceController(
            $serviceLocator->get(HelpdeskAccountAdminService::class),
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(TesterGroupAuthorisationMapper::class),
            $serviceLocator->get(UserAdminSessionService::class),
            $serviceLocator->get(PersonRoleManagementService::class),
            $serviceLocator->get(ContextProvider::class)
        );
    }
}
