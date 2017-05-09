<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Support;

use Zend\View\Helper\AbstractHelper;

class TwoFaFeatureToggleViewHelper extends AbstractHelper
{
    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    /**
     * @param TwoFaFeatureToggle $featureToggle
     */
    public function __construct(TwoFaFeatureToggle $featureToggle)
    {
        $this->twoFaFeatureToggle = $featureToggle;
    }

    /**
     * @return bool
     */
    public function __invoke()
    {
        return $this->twoFaFeatureToggle->isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->twoFaFeatureToggle->isEnabled();
    }
}
