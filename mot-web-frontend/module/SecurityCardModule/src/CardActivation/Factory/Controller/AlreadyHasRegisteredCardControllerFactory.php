<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\AlreadyHasRegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AlreadyHasRegisteredCardControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Controller\ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator($serviceLocator);
        $securityCardService = $serviceLocator->get(SecurityCardService::class);
        $contextProvider = $serviceLocator->get(ContextProvider::class);
        $personalDetailsService = $serviceLocator->get(ApiPersonalDetails::class);
        $catalogService = $serviceLocator->get(CatalogService::class);
        $identityProvider = $serviceLocator->get(MotIdentityProviderInterface::class);
        $securityCardGuard = $serviceLocator->get(SecurityCardGuard::class);

        return new AlreadyHasRegisteredCardController(
            $securityCardService,
            $contextProvider,
            $personalDetailsService,
            $catalogService,
            $identityProvider,
            $securityCardGuard
        );
    }
}
