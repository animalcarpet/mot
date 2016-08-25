<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Listener;


use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Listener\RegisterCardHardStopListener;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaFeature\FeatureToggles;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegisterCardHardStopListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotIdentityProviderInterface $motIdentityProvider */
        $motIdentityProvider = $serviceLocator->get('MotIdentityProvider');
        $hardStopCondition = $serviceLocator->get(RegisterCardHardStopCondition::class);

        return new RegisterCardHardStopListener(
            $motIdentityProvider,
            $hardStopCondition
        );
    }
}