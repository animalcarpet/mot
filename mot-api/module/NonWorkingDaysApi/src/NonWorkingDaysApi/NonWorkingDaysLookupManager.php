<?php

namespace NonWorkingDaysApi;

use NonWorkingDaysApi\Provider\NonWorkingDaysProvider;

/**
 * Takes the resposibility of managing non working days' lists while testing if a day is a non working one.
 *
 * Class NonWorkingDaysLookupManager
 */
class NonWorkingDaysLookupManager
{
    private $provider;
    private $nonWorkingDaysLookupStore = [];

    /**
     * @param NonWorkingDaysProvider $provider
     *                                         a component providing non working days list
     */
    public function __construct(NonWorkingDaysProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Checks if a given date is a non working day in a given country.
     *
     * @param \DateTime $date
     *                        a date the check is performed against
     * @param $countryCode
     *      a country identifier
     *
     * @return bool
     *              an answer whether a day is a non working day
     */
    public function isNonWorkingDay(\DateTime $date, $countryCode)
    {
        $year = $date->format('Y');

        if (!array_key_exists($year, $this->nonWorkingDaysLookupStore)) {
            $this->nonWorkingDaysLookupStore[$year] = $this->getHashedCollectionOfDateTimes($year, $countryCode);
        }
        $givenYearNonWorkingDays = $this->nonWorkingDaysLookupStore[$year];
        $nwdHash = self::calculateWorkingDayHash($date, $countryCode);
        $isNonWorkingDay = in_array($nwdHash, $givenYearNonWorkingDays);

        return $isNonWorkingDay;
    }

    /**
     * Calculate hash to ease off days lookups.
     *
     * @param \DateTime $date
     *                        date
     * @param $countryCode
     *      country code
     *
     * @return string
     *                a hash uniquely identifying a date in a given country
     */
    private static function calculateWorkingDayHash(\DateTime $date, $countryCode)
    {
        return $countryCode.$date->format('Ymd');
    }

    /**
     * @param $year
     * @param $countryCode
     *
     * @return array
     */
    private function getHashedCollectionOfDateTimes($year, $countryCode)
    {
        $datesColl = $this->provider->getNonWorkingDaysInYear($year, $countryCode);
        $hashedColl = [];
        foreach ($datesColl as $date) {
            $hashedColl[] = self::calculateWorkingDayHash($date, $countryCode);
        }

        return $hashedColl;
    }
}
