<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Action;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderProtectionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotIdentityProvider $identityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        /** @var TwoFaFeatureToggle $twoFaFeatureToggle */
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        $testerGroupAuthorisationMapper = $serviceLocator->get(TesterGroupAuthorisationMapper::class);

        $securityCardGuard = $serviceLocator->get(SecurityCardGuard::class);

        return new CardOrderProtection(
            $identityProvider,
            $securityCardGuard,
            $testerGroupAuthorisationMapper,
            $twoFaFeatureToggle
        );
    }
}
