<?php

namespace Core\Formatting;

use InvalidArgumentException;

class ComponentFailRateFormatter
{
    /**
     * Formats component fail rate in tester statistics
     * @param float $failRate
     * @return string
     */
    public static function format($failRate)
    {
        if(!is_numeric($failRate)) {
            throw new InvalidArgumentException("Can't format non-numeric data");
        }

        if(round($failRate, 1) <= 0 ){
            return "0";
        } else if (round($failRate, 1) >= 100){
            return "100";
        } else {
            return number_format($failRate, 1);
        }
    }
}
