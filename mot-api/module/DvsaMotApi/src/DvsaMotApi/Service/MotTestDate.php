<?php

namespace DvsaMotApi\Service;

use DateTime;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;

/**
 * Class MotTestDate.
 */
class MotTestDate
{
    // It *WILL TAKE* an Act of Parliament or something to have these values changed
    // so why slow the system down with a database call or such like just to read a
    // value that is essentially never going to change?
    const YEARS_BEFORE_FIRST_TEST_IS_DUE_CLASS_5 = 1;
    const YEARS_BEFORE_FIRST_TEST_IS_DUE = 3;
    const MOT_YEAR_DURATION = 1; // offset when extending an anniversary / awarded new MOT pass
    const MOT_DAY_DURATION = 1; // days to remove as part of MOT expiry date checking

    /** @var MotTest */
    private $motCurrent;
    /** @var MotTest */
    private $motPrevious;
    /** @var DateTime */
    private $testDate;

    /**
     * Constructs an MOT date assistant with the two given tests. The second test is optional
     * and depends on the history of the vehicle currently under inspection.
     *
     * @param DateTime $when     the test was conducted
     * @param MotTest  $current  MOT test for the vehicle under test
     * @param MotTest  $previous MOT test for the vehicle under test
     */
    public function __construct(DateTime $when, MotTest $current, MotTest $previous = null)
    {
        $this->testDate = $when;
        $this->motCurrent = $current;
        $this->motPrevious = $previous;
    }

    /**
     * The expiry date is the day before the "MOT due date". The current
     * certificate is valid up to 23:59 Hrs of the due date.
     *
     * This function, given an MOT test, will use the relevant date with
     * respect to previous tests, initial expiry periods etc. and return
     * the "YYYY-MM-DD" value as a DateTime instance that represents the
     * last day on which the certificate will be legally valid, up to
     * midnight as previously.
     *
     * @throws \Exception
     *
     * @return \DateTime|null
     */
    public function getExpiryDate()
    {
        if ($this->motPrevious) {
            return $this->withPreviousTest();
        } else {
            return $this->withNoPreviousTest();
        }
    }

    /**
     * When a vehicle has been tested before, the new expiry date is based upon the old expiry date
     * if presented within the preservation date period, otherwise it falls back to a standard
     * duration based upon the date of the test / contingency test.
     *
     * @return DateTime|null
     */
    private function withPreviousTest()
    {
        $expiryDate = null;
        $previousExpiry = $this->motPrevious->getExpiryDate();

        if ($previousExpiry && $this->isPreservable($previousExpiry)) {
            $expiryDate = self::asDatePlusOffset($this->motPrevious->getExpiryDate(), self::MOT_YEAR_DURATION, 0);
        } else {
            if (is_null($this->motCurrent->getEmergencyLog())) {
                $date = $this->motCurrent->getIssuedDate();

                if ($date) {
                    $expiryDate = self::getStandardDurationExpiryDate($date);
                } else {
                    $expiryDate = self::getStandardDurationExpiryDate($this->testDate);
                }
            } else {
                $date = $this->motCurrent->getStartedDate();

                if ($date) {
                    $expiryDate = self::getStandardDurationExpiryDate($date);
                }
            }
        }

        return $expiryDate;
    }

    /**
     * When there is no previous test on record the expiry date is based upon the vehicles
     * class, registered / manufactured date and wether or not we are contingency testing.
     *
     * @return DateTime|null
     */
    private function withNoPreviousTest()
    {
        $vehicle = $this->motCurrent->getVehicle();
        $notionalExpiryDate = $this->getNotionalExpiryDateForVehicle($vehicle);
        $expiryDate = null;

        if ($notionalExpiryDate && $this->isPreservable($notionalExpiryDate)) {
            if (is_null($this->motCurrent->getEmergencyLog())) {
                $expiryDate = self::asDatePlusOffset($notionalExpiryDate, self::MOT_YEAR_DURATION, 0);
            } else {
                $expiryDate = self::asDatePlusOffset($notionalExpiryDate, self::MOT_YEAR_DURATION, 0);
            }
        } else {
            if (is_null($this->motCurrent->getEmergencyLog())) {
                $expiryDate = self::getStandardDurationExpiryDate($this->testDate);
            } else {
                $expiryDate = self::getStandardDurationExpiryDate($this->motCurrent->getStartedDate());
            }
        }

        return $expiryDate;
    }

    /**
     * The notional expiry date is the "initial implied MOT expiry date" awarded to a vehicle by
     * dint of it being a newly manufactured vehicle or registered as new. We always need this
     * in case an MOT was performed and passed within the notional expiry date range as any such
     * tests should not cause the notional expiry date to be shortened.
     *
     * @param  $vehicle // (Vehicle or DvlaVehicle - there is no Base class at this time)
     *
     * @return \DateTime
     *
     * @throws \Exception
     */
    public static function getNotionalExpiryDateForVehicle($vehicle)
    {
        if ($vehicle->isDvla()) {
            return null;
        } else {
            return self::getNotionalExpiryForNonDvlaVehicle($vehicle);
        }
    }

    /**
     * For a non-DVLA vehicle we will have all required information (vehicle class etc)
     * to be able to get a notional expiry date.
     *
     * @param Vehicle $vehicle
     *
     * @return DateTime
     *
     * @throws \Exception
     */
    private static function getNotionalExpiryForNonDvlaVehicle(Vehicle $vehicle)
    {
        $vehicleClass = $vehicle->getVehicleClass();

        if ($vehicleClass instanceof VehicleClass) {
            if ($vehicle->isVehicleNewAtFirstRegistration()) {
                $date = $vehicle->getFirstRegistrationDate();
            } else {
                $date = $vehicle->getManufactureDate();

                if (!$date) {
                    $date = $vehicle->getFirstRegistrationDate();
                }

                if (!$date) {
                    $date = $vehicle->getFirstUsedDate();
                }
            }

            if (!$date instanceof \DateTime) {
                throw new \Exception("Registration or manufacture date is required for vehicle [{$vehicle->getId()}]");
            }

            $vehicleClassCode = (string) $vehicleClass->getCode();

            if (empty($vehicleClassCode)) {
                throw new \Exception("Vehicle class *CODE* is required for vehicle [{$vehicle->getId()}]");
            }

            $years = ('5' == $vehicleClassCode)
                ? self::YEARS_BEFORE_FIRST_TEST_IS_DUE_CLASS_5
                : self::YEARS_BEFORE_FIRST_TEST_IS_DUE;

            return self::asDatePlusOffset($date, $years, 1);
        } else {
            throw new \Exception("Vehicle class is required for vehicle [{$vehicle->getId()}]");
        }
    }

    /**
     * Answers TRUE if the expiry date of the given test is within the derived preservation
     * date window i.e. within a "month" of the expiry date.
     *
     * @param DateTime $date
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function isPreservable(\DateTime $date)
    {
        $alternativeToday = null;

        if ($this->motCurrent->getEmergencyLog()) {
            $alternativeToday = $this->motCurrent->getStartedDate();
        }

        // Clamp expiry to the END-Of-DAY in case the test was performed on this day
        $expiryDate = clone $date;
        $expiryDate->setTime(23, 59, 59);

        // Clamp today to the absolute start of the dat for maximal comparison range
        $today = clone $this->testDate;
        $today->setTime(0, 0, 0);

        $preservationDate = self::preservationDate($expiryDate);

        if (is_null($alternativeToday)) {
            return
                ($today >= $preservationDate)
                &&
                ($today < $expiryDate)
            ;
        } else {
            $today = clone $alternativeToday;
            $today->setTime(0, 0, 0);

            return
                ($today >= $preservationDate)
                &&
                ($today < $expiryDate)
            ;
        }
    }

    /**
     * Answers a date time as a standard MOT duration up to midnight.
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    public static function getStandardDurationExpiryDate(\DateTime $date = null)
    {
        if ($date) {
            return self::asDatePlusOffset($date, self::MOT_YEAR_DURATION, self::MOT_DAY_DURATION);
        }

        return null;
    }

    /**
     * Modifies a date backwards in time by a number years and days when supplied.
     *
     * The years are *added* to the input date.
     * The days are *subtracted* from the input date.
     * The time is set to 00:00:00 Hrs
     *
     * NOTE: The output date is a clone of the input date so as to leave the original
     * intact in case that is important to the caller.
     *
     * @param \DateTime $dateIn
     * @param int       $years
     * @param int       $days
     *
     * @return \DateTime
     */
    public static function asDatePlusOffset(\DateTime $dateIn, $years = 0, $days = 0)
    {
        /** @var \DateTime $dateOut */
        $dateOut = clone $dateIn;

        if ($years) {
            $dateOut->add(new \DateInterval('P'.$years.'Y'));
        }
        if ($days) {
            $dateOut->sub(new \DateInterval('P'.$days.'D'));
        }

        return $dateOut->setTime(0, 0, 0);
    }

    /**
     * Calculate a preservation date based on the given expiry date.
     *
     * Given an arbitrary expiry date for an MOT it will correctly
     * calculate the preservation date i.e. the earliest date that a
     * vehicle can be presented for testing.
     *
     * NOTE: Returned DateTime is clamped to 00:00:00 Hrs i.e the
     * start of the day.
     *
     * @param \DateTime $expiryDate
     *
     * @return \DateTime|null
     */
    public static function preservationDate(\DateTime $expiryDate = null)
    {
        if ($expiryDate) {
            $clonedDate = clone $expiryDate;
            $resultDate = null;

            $year = $clonedDate->format('Y');
            $month = $clonedDate->format('m');
            $day = $clonedDate->format('d');

            switch (strtoupper($clonedDate->format('Md'))) {
                case 'MAR27':
                    $resultDate = new \DateTime("{$year}-02-28");
                    break;

                case 'MAR28':
                    $resultDate = self::isLeapYear($year)
                        ? new \DateTime("{$year}-02-29")
                        : new \DateTime("{$year}-03-01");
                    break;

                case 'MAR29':
                case 'MAR30':
                case 'MAR31':
                    $resultDate = new \DateTime("{$year}-03-01");
                    break;

                default:
                    $resultDate = ('31' === $day)
                        ? new \DateTime("{$year}-{$month}-01")
                        : $clonedDate->sub(new \DateInterval('P1M'))->add(new \DateInterval('P1D'));
                    break;
            }

            return $resultDate->setTime(0, 0, 0);
        }

        return null;
    }

    /**
     * Fast helper for checking if a year is a leap year or not outside
     * of any other form of date wrapper.
     *
     * @param $year
     *
     * @return bool
     */
    public static function isLeapYear($year)
    {
        return
            (($year % 4) === 0)
            && ((($year % 100) !== 0) || (($year % 400) === 0))
        ;
    }
}
