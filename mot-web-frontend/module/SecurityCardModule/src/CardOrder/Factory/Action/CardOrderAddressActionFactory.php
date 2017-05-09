<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderAddressAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderAddressActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var OrderNewSecurityCardSessionService $securityCardSessionService */
        $securityCardSessionService = $serviceLocator->get(OrderNewSecurityCardSessionService::class);

        /** @var OrderSecurityCardAddressService $orderSecurityCardAddressService */
        $orderSecurityCardAddressService = $serviceLocator->get(OrderSecurityCardAddressService::class);

        /** @var OrderSecurityCardStepService $orderSecurityCardStepService */
        $orderSecurityCardStepService = $serviceLocator->get(OrderSecurityCardStepService::class);

        $cardOrderProtection = $serviceLocator->get(CardOrderProtection::class);

        return new CardOrderAddressAction(
            $orderSecurityCardAddressService,
            $securityCardSessionService,
            $orderSecurityCardStepService,
            $cardOrderProtection
        );
    }
}
