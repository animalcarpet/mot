<?php

namespace NonWorkingDaysApi\Provider;

use DvsaEntities\Repository\NonWorkingDayRepository;

class HolidaysProvider implements NonWorkingDaysProvider
{
    /**
     * @var NonWorkingDayRepository
     */
    private $nonWorkingDayLookupRepository;

    public function __construct(NonWorkingDayRepository $nonWorkingDayLookupRepository)
    {
        $this->nonWorkingDayLookupRepository = $nonWorkingDayLookupRepository;
    }

    /**
     * @param int    $year
     * @param string $countryCode
     *
     * @return \DateTime[]
     */
    public function getNonWorkingDaysInYear($year, $countryCode)
    {
        return $this->nonWorkingDayLookupRepository->findDaysByCountryAndYear($countryCode, $year);
    }
}
