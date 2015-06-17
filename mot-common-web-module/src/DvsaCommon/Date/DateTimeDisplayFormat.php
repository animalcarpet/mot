<?php

namespace DvsaCommon\Date;

/**
 * Formats a date, datetime and time in a display-ready way.
 * Converts datetime and time to user timezone
 */
class DateTimeDisplayFormat
{

    const FORMAT_DATETIME_SHORT = 'j M Y, g:ia';
    const FORMAT_DATETIME = 'j F Y, g:ia';
    const FORMAT_DATE = 'j F Y';
    const FORMAT_TIME = 'g:ia';
    const FORMAT_MONTH_YEAR = 'M Y';

    /**
     * Outputs date and time according to GDS UX guidelines
     *
     * @param \DateTime $dateTime
     *
     * @return string
     */
    public static function dateTime($dateTime)
    {
        return is_null($dateTime) ? '' : DateUtils::toUserTz($dateTime)->format(self::FORMAT_DATETIME);
    }

    /**
     * Outputs date and time according to GDS UX guidelines
     *
     * @param \DateTime $dateTime
     *
     * @return string
     */
    public static function dateTimeShort($dateTime)
    {
        return is_null($dateTime) ? '' : DateUtils::toUserTz($dateTime)->format(self::FORMAT_DATETIME_SHORT);
    }


    /**
     * Outputs date and time according to GDS UX guidelines
     *
     * @param string $textDateTime parsable date time representation (see DateUtils::toDateTime)
     *
     * @return string
     */
    public static function textDateTime($textDateTime)
    {
        return is_null($textDateTime) ? '' : self::dateTime(DateUtils::toDateTime($textDateTime));
    }


    /**
     * Outputs short date and time according to GDS UX guidelines
     *
     * @param string $textDateTime parsable date time representation (see DateUtils::toDate)
     *
     * @return string
     */
    public static function textDateTimeShort($textDateTime)
    {
        return is_null($textDateTime) ? '' : self::dateTimeShort(DateUtils::toDateTime($textDateTime));
    }

    /**
     * Outputs date according to UX guidelines
     *
     * @param string $textDate
     *
     * @return string|null
     */
    public static function textDate($textDate)
    {
        return is_null($textDate) ? '' : self::date(DateUtils::toDateTime($textDate, false));
    }


    /**
     * Outputs date according to UX guidelines
     *
     * @param \DateTime $date
     *
     * @return string
     */
    public static function date($date)
    {
        return is_null($date) ? '' : $date->format(self::FORMAT_DATE);
    }

    /**
     * Outputs date according to UX guidelines
     *
     * @param \DateTime|Time $time
     *
     * @return string
     */
    public static function time($time)
    {
        if (is_null($time)) {
            return '';
        }
        // \DvsaCommon\Date\Time class is agnostic to timezones
        if ($time instanceof Time) {
            return $time->format(self::FORMAT_TIME);
        } else {
            return DateUtils::toUserTz($time)->format(self::FORMAT_TIME);
        }
    }

    /**
     * Outputs date format Month Year according to UX guidelines
     * Eg: Fev 2015
     *
     * @param \DateTime $time
     * @return string
     */
    public static function toMonthYear($time)
    {
        if (is_null($time)) {
            return '';
        }
        return DateUtils::toUserTz($time)->format(self::FORMAT_MONTH_YEAR);
    }

    /**
     * Returns formatted current date
     * @return string
     */
    public static function nowAsDate()
    {
        return self::date(DateUtils::toUserTz(new \DateTime));
    }

    /**
     * Returns formatted current date and time
     * @return string
     */
    public static function nowAsDateTime()
    {
        return self::dateTime(new \DateTime);
    }
}
