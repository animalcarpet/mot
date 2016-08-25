<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Support;

use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;

class TwoFaFeatureToggleTest extends \PHPUnit_Framework_TestCase
{

    public function testIsEnabled() {

        $this->assertTrue((new TwoFaFeatureToggle(new FeatureToggles([FeatureToggle::TWO_FA => true])))->isEnabled());
    }
}