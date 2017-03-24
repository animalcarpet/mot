<?php

namespace DvsaCommon\Date;

use DateTime;
use DateTimeInterface;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Date\Exception\NonexistentDateException;
use DvsaCommon\Date\Exception\NonexistentDateTimeException;
use DvsaCommon\Utility\TypeCheck;

/**
 * Util class for date operations.
 *
 * Use it instead of raw \DateTime class (which is very doggy)
 */
final class DateUtils
{
    const FORMAT_ISO_WITH_TIME = 'Y-m-d H:i:s';
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    private static $FORMAT_DATETIME_DESC = 'yyyy-mm-ddThh:mm:ss(Z|+hh:mm)';
    private static $FORMAT_DATE_DESC = 'yyyy-mm-dd';
    private static $USER_TZ = 'Europe/London';
    private static $FORMAT_ISO8601TZ = '/^(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})(T(?P<hour>\d{2}):(?P<min>\d{2}):(?P<sec>\d{2})(?:Z|(?:[+-]\d{2}(:(\d{2}))?)))?$/';
    private static $FORMAT_ISO8601_DATE = '/^(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})$/';

    const FIRST_OF_THIS_MONTH = 'first day of this month';

    /**
     * Returns next calendar day with optional crop of time.
     *
     * @param \DateTime $date
     *                                the date you want to have the next day of
     * @param bool      $preserveTime
     *                                flag true|false whether to crop time part of the timestamp
     *
     * @return \DateTime
     *                   the next calendar day
     */
    public static function nextDay(\DateTime $date, $preserveTime = true)
    {
        $newDate = clone $date;
        if (false === $preserveTime) {
            $newDate = self::cropTime($newDate);
        }
        $newDate->add(new \DateInterval("P1D"));

        return $newDate;
    }

    /**
     * Returns the first day of the current month.
     *
     * @return \DateTime
     *                   the first day of the month
     */
    public static function firstOfThisMonth()
    {
        return self::cropTime((new \DateTime())->modify(self::FIRST_OF_THIS_MONTH));
    }

    /**
     * @param DateTimeInterface $datetime timestamp
     *
     * @return DateTime timestamp with time part reset
     */
    public static function cropTime(DateTimeInterface $datetime)
    {
        if ($datetime instanceof DateTime) {
            $newDateTime = clone $datetime;
        } else {
            $newDateTime = DateTime::createFromFormat(DateTime::ISO8601, $datetime->format(DateTime::ISO8601),
                $datetime->getTimezone());
        }

        $newDateTime->setTime(0, 0, 0);

        return $newDateTime;
    }

    /**
     * Returns information if a given DateTime is a weekend.
     *
     * @param \DateTime $dateTime
     *
     * @return bool
     */
    public static function isWeekend(\DateTime $dateTime)
    {
        $dayOfWeek = $dateTime->format('w');

        return $dayOfWeek == 0 || $dayOfWeek == 6;
    }

    /**
     * @param $value
     * @param $length
     *
     * @throws Exception\IncorrectDateFormatException
     *
     * @return string
     */
    private static function parseNumericString($value, $length)
    {
        if (false === is_numeric($value)) {
            throw new IncorrectDateFormatException('numeric', $value);
        }

        return str_pad($value, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Checks if a date (without time) is in the future.
     *
     * @param \DateTime $dateTime
     *
     * @return bool
     */
    public static function isDateInFuture(\DateTime $dateTime)
    {
        return self::cropTime($dateTime) > self::cropTime(new \DateTime());
    }

    /**
     * checks if $date is between $startDate and $endDate.
     *
     * @param \DateTime $date
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @throws Exception\DateException
     *
     * @return boolean
     */
    public static function isDateTimeBetween(\DateTime $date, \DateTime $startDate, \DateTime $endDate)
    {
        if ($startDate > $endDate) {
            throw new DateException('Start date cannot be greater than end date');
        }

        return ($date >= $startDate && $date <= $endDate);
    }

    /**
     * checks if $date is between $startDate and $endDate.
     *
     * @param \DateTime $date
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @throws Exception\DateException
     *
     * @return boolean
     */
    public static function isDateBetween(\DateTime $date, \DateTime $startDate, \DateTime $endDate)
    {
        if (self::compareDates($startDate, $endDate) > 0) {
            throw new DateException('Start date cannot be greater than end date');
        }

        return self::compareDates($date, $startDate) >= 0 && self::compareDates($date, $endDate) <= 0;
    }

    /**
     * Returns integer number of days between two input dates.
     * Negative if second date is before first date.
     *
     * @param string|\DateTime $date1
     * @param string|\DateTime $date2
     *
     * @return string
     */
    public static function getDaysDifference($date1, $date2)
    {
        $dateTime1 = $date1 instanceof \DateTime ? $date1 : self::toDate($date1);
        $dateTime2 = $date2 instanceof \DateTime ? $date2 : self::toDate($date2);

        return $dateTime1->diff($dateTime2)->format('%r%a');
    }

    /**
     * Gets the absolute number of seconds between two DateTimes.
     *
     * @param \DateTime $datetime1
     * @param \DateTime $datetime2
     *
     * @return number
     */
    public static function getTimeDifferenceInSeconds($datetime1, $datetime2)
    {
        $seconds = abs($datetime1->getTimestamp() - $datetime2->getTimestamp());

        return $seconds;
    }

    /**
     * Provided with a number of seconds, calculates the right number of years, days etc and creates a DateInterval.
     *
     * @param $seconds
     *
     * @return bool|\DateInterval
     */
    public static function convertSecondsToDateInterval($seconds)
    {
        $years = floor($seconds / 31536000);
        $days = floor(($seconds % 31536000) / 86400);
        $hours = floor((($seconds % 31536000) % 86400) / 3600);
        $minutes = floor(((($seconds % 31536000) % 86400) % 3600) / 60);
        $seconds = ((($seconds % 31536000) % 86400) % 3600) % 60;

        $interval = new \DateInterval(
            'P' . $years . 'Y' . $days . 'DT' . $hours . 'H' . $minutes . 'M' . $seconds . 'S'
        );

        return $interval;
    }

    /**
     * Calculates only number of minutes and seconds in given number of seconds
     * @param $seconds number of seconds
     * @return array with 'minutes' => total number of minutes, 'seconds' => seconds
     */
    public static function getMinutesAndSecondsFromSeconds($seconds)
    {
        return [
            'minutes' => (int) floor($seconds / 60),
            'seconds' => $seconds % 60,
        ];
    }

    /**
     * Returns date on a set number of calendar months before a given date.
     * Equal to the date on the same day in a prior month: if the date does
     * not exist the end date of that month is used.
     *
     * @param \DateTime $dateTime
     * @param           $monthsToSubtract
     *
     * @return \DateTime
     */
    public static function subtractCalendarMonths(\DateTime $dateTime, $monthsToSubtract)
    {
        $year = $dateTime->format('Y');
        $month = $dateTime->format('m');
        $day = $dateTime->format('d');

        $year -= floor($monthsToSubtract / 12);
        $monthsToSubtract = $monthsToSubtract % 12;
        $month -= $monthsToSubtract;
        if ($month <= 0) {
            $year--;
            $month = $month % 12;
            if ($month === 0) {
                $month = 12;
            } else {
                if ($month < 0) {
                    $month = 12 + $month;
                }
            }
        }

        if (checkdate($month, $day, $year)) {
            $resultDateTime = self::toDateFromParts($day, $month, $year);
        } else {
            //date does not exist so use the last day of the month.
            $resultDateTime = self::toDateFromParts(1, $month, $year);
            $resultDateTime->modify('last day of');
        }

        return $resultDateTime;
    }

    /**
     * @param $day
     * @param $month
     * @param $year
     *
     * @throws Exception\NonexistentDateException
     * @throws Exception\IncorrectDateFormatException
     *
     * @return bool
     */
    public static function validateDateByParts($day, $month, $year)
    {
        if (strlen($day) !== 2 || strlen($month) !== 2 || strlen($year) !== 4) {
            throw new IncorrectDateFormatException('dd/mm/yyyy', $day . '/' . $month . '/' . $year);
        }

        if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year)) {
            throw new IncorrectDateFormatException('dd/mm/yyyy', $day . '/' . $month . '/' . $year);
        }

        if (checkdate($month, $day, $year) === false) {
            throw new NonexistentDateException($day . '/' . $month . '/' . $year);
        }

        return true;
    }

    /**
     * @deprecated
     * Returns date string in simplified ISO 8601 format (yyyy-mm-dd H:i:s)
     *
     * requires 1 (\DataTime) or 3 (string $day, string $month, string $year) arguments
     *
     * @throws Exception\IncorrectDateFormatException
     * @throws Exception\DateException
     *
     * @return string
     */
    public static function toIsoString()
    {
        $argumentCount = func_num_args();

        if (1 === $argumentCount) {
            $dateTimeObject = func_get_arg(0);
            if (is_a($dateTimeObject, \DateTime::class)) {
                return $dateTimeObject->format(self::FORMAT_ISO_WITH_TIME);
            } else {
                if (is_object($dateTimeObject)) {
                    $type = get_class($dateTimeObject);
                } else {
                    $type = gettype($dateTimeObject);
                }
                throw new DateException(
                    'DateUtils::toIsoString invalid argument type. Expected \DateTime, given ' . $type
                );
            }
        }

        throw new DateException('DateUtils::toIsoString expected exactly one argument given ' . $argumentCount);
    }

    /**
     * Gets 3 values from passed array, concatenate them to ISO string format (see DateUtils class) and returns it.
     * This method does NOT verify if returned date is valid.
     *
     * WARNING: 3 keys (args 2, 3 and 4) are removed from array
     *
     * @param &$data array
     * @param $day   string numeric
     * @param $month string numeric
     * @param $year  string numeric
     *
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function concatenateDateStringAndRemoveKeysFromArray(&$data, $day, $month, $year)
    {
        TypeCheck::assertArray($data);

        foreach ([$day => 2, $month => 2, $year => 4] as $key => $length) {
            if (false === array_key_exists($key, $data)) {
                throw new \OutOfBoundsException($key . ' not found in array');
            }

            $data[$key] = self::parseNumericString($data[$key], $length);
        }

        $result = $data[$year] . '-' . $data[$month] . '-' . $data[$day];
        unset($data[$day], $data[$month], $data[$year]);

        return $result;
    }

    /**
     * Converts \DateTime to user timezone.
     *
     * @param \DateTime $dateTime
     *
     * @return \DateTime
     */
    public static function toUserTz(\DateTime $dateTime)
    {
        $dateTimeCopy = clone $dateTime;

        return $dateTimeCopy->setTimezone(new \DateTimeZone(self::$USER_TZ));
    }

    /**
     * Converts DateTime to timestamp including timezone offset.
     *
     * @param \DateTime $dateTime
     *
     * @return int
     */
    public static function toUserTzTimestamp(\DateTime $dateTime)
    {
        return $dateTime->getTimestamp() + self::toUserTz($dateTime)->getOffset();
    }

    public static function compareDates(\DateTime $date1, \DateTime $date2)
    {
        $croppedDate1 = self::cropTime($date1);
        $croppedDate2 = self::cropTime($date2);
        $isoDate1 = $croppedDate1->format('Y-m-d');
        $isoDate2 = $croppedDate2->format('Y-m-d');

        return ($isoDate1 < $isoDate2) ? -1 : (($isoDate1 > $isoDate2) ? 1 : 0);
    }

    /**
     * Returns current date.
     *
     * @return \DateTime
     */
    public static function today()
    {
        return self::toDate(self::nowAsUserDateTime()->format('Y-m-d'));
    }

    /**
     * Checks if date is today
     *
     * @param string|DateTime $date
     * @return bool
     */
    public static function isToday($date)
    {
        $date = $date instanceof DateTime ? $date : DateUtils::toDateTime($date, false);
        $today = DateUtils::nowAsUserDateTime();

        return ($date->format("Y") == $today->format("Y"))
            && ($date->format("m") == $today->format("m"))
            && ($date->format("d") == $today->format("d"));
    }

    /**
     * Returns current date and time in user timezone.
     *
     * @return \DateTime
     */
    public static function nowAsUserDateTime()
    {
        return self::toUserTz(new \DateTime());
    }

    /**
     * Converts datetime to UTC timezone.
     *
     * @param \DateTime $dateTime
     *
     * @return \DateTime
     */
    public static function toUtc(\DateTime $dateTime)
    {
        $dateTimeCopy = clone $dateTime;

        return $dateTimeCopy->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * Return true if input has valid date format YYYY-MM-DD.
     *
     * @param $strDate
     *
     * @return bool
     */
    public static function isValidDate($strDate)
    {
        try {
            self::toDate($strDate);
        } catch (DateException $e) {
            return false;
        }

        return true;
    }

    /**
     * Parses string input (see $FORMAT_DATE_DESC) to DateTime object.
     *
     * @param string $input
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     * @throws \DvsaCommon\Date\Exception\NonexistentDateException
     *
     * @return \DateTime
     */
    public static function toDate($input)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException("Function argument is not a string!");
        }
        if (!preg_match(self::$FORMAT_ISO8601_DATE, $input, $m)) {
            throw new IncorrectDateFormatException(self::$FORMAT_DATE_DESC, $input);
        }
        self::checkDate($m['year'], $m['month'], $m['day']);

        return self::strDateAsDate($input);
    }

    private static function strDateAsDate($strDate)
    {
        return self::cropTime(new \DateTime($strDate));
    }

    public static function toDateFromParts($day, $month, $year)
    {
        if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year) || strlen($year) !== 4) {
            throw new IncorrectDateFormatException('numeric: day, month, year', 'wrong params length or type');
        }

        $day = self::parseNumericString($day, 2);
        $month = self::parseNumericString($month, 2);

        return self::toDate($year . '-' . $month . '-' . $day);
    }

    public static function toDateTimeFromParts($year, $month, $day, $hour = 0, $minute = 0, $second = 0)
    {
        $dateTimeStr = sprintf('%04s-%02s-%02sT%02s:%02s:%02sZ', $year, $month, $day, $hour, $minute, $second);

        if (strlen($year) !== 4 || !preg_match(self::$FORMAT_ISO8601TZ, $dateTimeStr)) {
            throw new IncorrectDateFormatException(
                'numeric: day, month, year, hour, minute, second',
                'wrong params length or type'
            );
        }

        return self::toDateTime($dateTimeStr);
    }

    /**
     * Parses string input (see $FORMAT_DATETIME_DESC) to DateTime object.
     *
     * @param string  $input
     * @param boolean $strict
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     * @throws \DvsaCommon\Date\Exception\NonexistentDateException
     * @throws \DvsaCommon\Date\Exception\NonexistentDateTimeException
     *
     * @return \DateTime
     */
    public static function toDateTime($input, $strict = true)
    {
        self::validateParserInputType($input);

        if (!preg_match(self::$FORMAT_ISO8601TZ, $input, $m)) {
            throw new IncorrectDateFormatException(self::$FORMAT_DATETIME_DESC, $input);
        }
        list($year, $month, $day) = [$m['year'], $m['month'], $m['day']];
        self::checkDate($year, $month, $day);

        $isTimeDefined = !empty($m['hour']);
        if ($isTimeDefined) {
            list($hour, $min, $sec) = [$m['hour'], $m['min'], $m['sec']];
            if (intval($hour) > 24 || intval($min) > 59 || intval($sec) > 59) {
                throw new NonexistentDateTimeException("$year-$month-$day $hour:$min:$sec");
            }

            return DateUtils::toUtc(new \DateTime($input));
        } elseif (!$strict) {
            return self::strDateAsDate($input);
        } else {
            throw new IncorrectDateFormatException(self::$FORMAT_DATETIME_DESC, $input);
        }
    }

    private static function validateParserInputType($arg)
    {
        if (!is_string($arg)) {
            throw new \InvalidArgumentException("Function argument is not a string!");
        }
    }

    private static function checkDate($year, $month, $day)
    {
        $invalidDate = !checkdate(intval($month), intval($day), intval($year));
        if ($invalidDate) {
            throw new NonexistentDateException("$year-$month-$day");
        }
    }

    /**
     * Calculate timestamp delta between 2 dates.
     *
     * @param \DateTime $dateTo
     * @param \DateTime $dateFrom
     *
     * @return int
     */
    public static function getDatesTimestampDelta(\DateTime $dateTo, \DateTime $dateFrom)
    {
        return DateUtils::toUserTzTimestamp($dateTo) - DateUtils::toUserTzTimestamp($dateFrom);
    }

    /**
     * Checks if a date (without time) is in the past.
     *
     * @param \DateTime $dateTime
     *
     * @return bool
     */
    public static function isDateInPast(\DateTime $dateTime)
    {
        return self::cropTime($dateTime) < self::cropTime(new \DateTime());
    }

    /**
     * Accepts date in one format and returns in another.
     *
     * @param string $input
     * @param string $inputFormat
     * @param string $outputFormat
     *
     * @return string
     */
    public static function changeFormat($input, $inputFormat, $outputFormat)
    {
        $date = \DateTime::createFromFormat($inputFormat, $input);

        return $date->format($outputFormat);
    }

    public static function roundUp(\DateTime $dateTime)
    {
        $date = self::cropTime($dateTime);

        if ($dateTime != $date) {
            $date = $date->modify('+ 1 day');
        }

        return $date;
    }
}
