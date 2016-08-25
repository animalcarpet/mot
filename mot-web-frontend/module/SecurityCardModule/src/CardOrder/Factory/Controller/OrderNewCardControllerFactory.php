<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderNewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\OrderNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProvider;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrderNewCardControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrderNewCardController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $action = $serviceLocator->get(CardOrderNewAction::class);

        /** @var MotIdentityProvider $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new OrderNewCardController(
            $action,
            $identityProvider->getIdentity()
        );
    }
}
