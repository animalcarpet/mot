<?php

namespace DvsaCommon\Date;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

/**
 * Injectable DateTime container that SHOULD be used to access current date time.
 * When working with dates in test you have to be able to mock out real date and time to avoid
 * test inconsistency and unpredictability
 *
 * Class DateTimeHolder
 *
 * @package DvsaCommon\Date
 */
class DateTimeHolder implements DateTimeHolderInterface, AutoWireableInterface
{
    /**
     * Returns current datetime
     *
     * @return \DateTime
     */
    public function getCurrent($withMilliseconds = false)
    {
        if ($withMilliseconds === true) {
            $microTime = microtime(true);

            return new \DateTime(
                date('Y-m-d H:i:s.'.(string)ceil(($microTime - floor($microTime))*1000000), $microTime)
            );

        }
        return new \DateTime();
    }

    /**
     * Returns current date (with time part zeroed)
     * @return \DateTime
     */
    public function getCurrentDate()
    {
        return DateUtils::cropTime($this->getCurrent());
    }

    public function getTimestamp($asFloat = false)
    {
        return microtime($asFloat);
    }

    /**
     * Returns current user datetime
     *
     * @return \DateTime
     */
    public function getUserCurrent($withMilliseconds = false)
    {
        if ($withMilliseconds === true) {
            $microTime = microtime(true);

            return new \DateTime(
                date('Y-m-d H:i:s.'.(string)ceil(($microTime - floor($microTime))*1000000), $microTime),
                new \DateTimeZone('Europe\London')
            );

        }
        return new \DateTime('now', new \DateTimeZone('Europe/London'));
    }

    /**
     * Returns current date (with time part zeroed)
     * @return \DateTime
     */
    public function getUserCurrentDate()
    {
        return DateUtils::cropTime($this->getUserCurrent());
    }
}
