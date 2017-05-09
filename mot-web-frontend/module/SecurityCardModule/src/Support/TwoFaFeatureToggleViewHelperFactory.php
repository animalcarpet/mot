<?php
/**
 * This file is part of the DVSA MOT FeatureToggle package.
 *
 * @link http://gitlab.clb.npm/mot/featuretoggle
 */

namespace Dvsa\Mot\Frontend\SecurityCardModule\Support;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TwoFaFeatureToggleViewHelperFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        $serviceManager = $pluginManager->getServiceLocator();
        $twoFaFeatureToggle = $serviceManager->get(TwoFaFeatureToggle::class);

        return new TwoFaFeatureToggleViewHelper($twoFaFeatureToggle);
    }
}
