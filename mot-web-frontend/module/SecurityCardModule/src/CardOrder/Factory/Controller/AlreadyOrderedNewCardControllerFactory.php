<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\AlreadyOrderedNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotIdentityProvider;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;

class AlreadyOrderedNewCardControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AlreadyOrderedNewCardController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        /** @var MotIdentityProvider $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        /** @var SecurityCardService $securityCardService */
        $securityCardService = $serviceLocator->get(SecurityCardService::class);

        /** @var TwoFaFeatureToggle */
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new AlreadyOrderedNewCardController(
            $request,
            $identityProvider->getIdentity(),
            $securityCardService,
            $twoFaFeatureToggle
        );
    }
}
