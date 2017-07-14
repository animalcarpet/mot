<?php

namespace Core\Formatting;

class FailedTestsPercentageFormatter
{
    public function format($value)
    {
        $rounded = round($value, 0, PHP_ROUND_HALF_UP);

        return $rounded.'%';
    }
}
