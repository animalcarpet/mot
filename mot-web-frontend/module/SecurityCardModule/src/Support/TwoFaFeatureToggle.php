<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Support;

use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;

class TwoFaFeatureToggle
{
    private $toggles;

    public function __construct(FeatureToggles $toggles)
    {
        $this->toggles = $toggles;
    }

    public function isEnabled()
    {
        return $this->toggles->isEnabled(FeatureToggle::TWO_FA);
    }
}