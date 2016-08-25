<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderAddressController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderConfirmationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProvider;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;

class CardOrderConfirmationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CardOrderAddressController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var OrderNewSecurityCardSessionService $securityCardSessionService */
        $securityCardSessionService = $serviceLocator->get(OrderNewSecurityCardSessionService::class);

        /** @var MotIdentityProvider $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new CardOrderConfirmationController(
            $securityCardSessionService,
            $identityProvider->getIdentity()
        );
    }
}
