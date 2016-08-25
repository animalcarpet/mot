<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderNewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderNewActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var OrderNewSecurityCardSessionService $securityCardSessionService */
        $securityCardSessionService = $serviceLocator->get(OrderNewSecurityCardSessionService::class);

        /** @var OrderSecurityCardStepService $orderSecurityCardStepService */
        $orderSecurityCardStepService = $serviceLocator->get(OrderSecurityCardStepService::class);

        /** @var MotFrontendIdentityProvider $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        $cardOrderProtection = $serviceLocator->get(CardOrderProtection::class);

        return new CardOrderNewAction(
            $securityCardSessionService,
            $orderSecurityCardStepService,
            $identityProvider,
            $cardOrderProtection
        );
    }
}