<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderReviewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderReviewActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var OrderNewSecurityCardSessionService $securityCardSessionService */
        $securityCardSessionService = $serviceLocator->get(OrderNewSecurityCardSessionService::class);

        /** @var ApiPersonalDetails $apiPersonalDetails */
        $apiPersonalDetails = $serviceLocator->get(ApiPersonalDetails::class);

        /** @var SecurityCardService $securityCardService */
        $securityCardService = $serviceLocator->get(SecurityCardService::class);

        /** @var OrderSecurityCardStepService $orderSecurityCardStepService */
        $orderSecurityCardStepService = $serviceLocator->get(OrderSecurityCardStepService::class);

        $cardOrderProtection = $serviceLocator->get(CardOrderProtection::class);

        return new CardOrderReviewAction(
            $securityCardSessionService,
            $apiPersonalDetails,
            $securityCardService,
            $orderSecurityCardStepService,
            $cardOrderProtection
        );
    }
}