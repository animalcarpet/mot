<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegisterCardHardStopConditionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $identityProvider MotFrontendIdentityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        $featureToggles = $serviceLocator->get(FeatureToggles::class);

        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);

        return new RegisterCardHardStopCondition($featureToggles, $authorisationService, $identityProvider);
    }
}
