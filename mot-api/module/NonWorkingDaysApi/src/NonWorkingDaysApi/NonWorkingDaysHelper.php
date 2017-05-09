<?php

namespace NonWorkingDaysApi;

use DvsaCommon\Date\DateUtils;

/**
 * The purpose of this helper is to provide utility methods related to doing calculations on non working dates.
 */
class NonWorkingDaysHelper
{
    private $nonWorkingDaysLookupManager;

    /**
     * @param NonWorkingDaysLookupManager $nonWorkingDaysLookupManager
     *                                                                 a lookup utility for checking if a given date in a country is a non working day
     */
    public function __construct(NonWorkingDaysLookupManager $nonWorkingDaysLookupManager)
    {
        $this->nonWorkingDaysLookupManager = $nonWorkingDaysLookupManager;
    }

    /**
     * Calculate n-th working day starting from the day after given date.
     *
     * @param \DateTime $startDate     a reference date the check will be carried out from e.g. the current date
     * @param int       $nthWorkingDay n-th working day following the start date
     * @param string    $countryCode   the  code of country for which the working days will be retrieved
     *
     * @return \DateTime returns n-th working day after $startDate
     */
    public function calculateNthWorkingDayAfter(
        \DateTime $startDate,
        $nthWorkingDay,
        $countryCode
    ) {
        if ($nthWorkingDay <= 0) {
            throw new \InvalidArgumentException('Nth working day has to be greater than 0');
        }
        $workingDaysCounter = 0;
        $counterDate = $startDate;

        do {
            $counterDate = DateUtils::nextDay($counterDate);

            $isWeekend = DateUtils::isWeekend($counterDate);
            if (!$isWeekend) {
                $isNonWorkingDay = $this->nonWorkingDaysLookupManager->isNonWorkingDay($counterDate, $countryCode);
                if (!$isNonWorkingDay) {
                    ++$workingDaysCounter;
                }
            }
        } while ($workingDaysCounter < $nthWorkingDay);

        return $counterDate;
    }
}
