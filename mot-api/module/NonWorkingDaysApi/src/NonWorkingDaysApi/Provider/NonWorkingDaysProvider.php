<?php

namespace NonWorkingDaysApi\Provider;

/**
 * Interface that should be implemented by the class that provides retrieval logic
 * for non working days.
 *
 * Interface NonWorkingDaysProvider
 */
interface NonWorkingDaysProvider
{
    /**
     * Retrieves an array of \DateTime objects that represent non working days (excl. weekend)
     * in a given year and country.
     *
     * @param $year
     *      a year (string 'YYYY' format)
     * @param $countryCode
     *      a code that represents a country
     *
     * @return mixed
     */
    public function getNonWorkingDaysInYear($year, $countryCode);
}
