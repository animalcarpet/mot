<?php

namespace DvsaCommon\MysteryShopper;
use DateInterval;
use DateTime;

/**
 * MysteryShopperExpiryDateGenerator.
 */
class MysteryShopperExpiryDateGenerator
{
    const INTERVAL_OF_ONE_DAY = 'P1D';
    const INTERVAL_OF_ONE_YEAR = 'P1Y';

    /**
     * @return DateTime
     */
    public function getPreviousMotExpiryDate()
    {
        return (new DateTime())->add(new DateInterval(self::INTERVAL_OF_ONE_DAY));
    }

    /**
     * @return DateTime
     */
    public function getCertificateExpiryDate()
    {
        $fakeExpiryDate = $this->getPreviousMotExpiryDate();
        return $fakeExpiryDate->add(new DateInterval(self::INTERVAL_OF_ONE_YEAR));
    }
}
