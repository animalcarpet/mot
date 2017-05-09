<?php

namespace UserAdmin\Factory\Controller;

use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use UserAdmin\Controller\ResetAccountClaimByPostController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;

/**
 * Factory for {@link \UserAdmin\Controller\ResetAccountClaimByPostController}.
 */
class ResetAccountClaimByPostControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $appServiceLocator */
        $appServiceLocator = $controllerManager->getServiceLocator();

        /** @var HelpdeskAccountAdminService $accountAdminService */
        $accountAdminService = $appServiceLocator->get(HelpdeskAccountAdminService::class);
        /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
        $testerGroupAuthorisationMapper = $appServiceLocator->get(TesterGroupAuthorisationMapper::class);
        /** @var MotAuthorisationServiceInterface $authorisationService */
        $authorisationService = $appServiceLocator->get('AuthorisationService');

        return new ResetAccountClaimByPostController(
            $accountAdminService,
            $testerGroupAuthorisationMapper,
            $authorisationService
        );
    }
}
