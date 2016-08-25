<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Support;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TwoFaFeatureToggleFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');
        return new TwoFaFeatureToggle($featureToggles);
    }
}