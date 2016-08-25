<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderAddressAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\CardOrderAddressController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProvider;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;

class CardOrderAddressControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CardOrderAddressController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $orderSecurityCardAddressService = $serviceLocator->get(OrderSecurityCardAddressService::class);

        $action = $serviceLocator->get(CardOrderAddressAction::class);

        /** @var MotIdentityProvider $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new CardOrderAddressController(
            $orderSecurityCardAddressService,
            $action,
            $identityProvider->getIdentity()
        );
    }
}
