<?php

namespace Dvsa\Mot\Frontend\TestQualityInformation\Formatting;

class TqiFailedTestsPercentageFormatter
{
    public function format($value)
    {
        $rounded = round($value, 0, PHP_ROUND_HALF_UP);

        return $rounded . '%';
    }
}
