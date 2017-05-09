<?php

namespace DvsaAuthentication\Identity\OpenAM\Utils;

class PasswordExpiryAttributeParser
{
    public static function parse($value)
    {
        try {
            return self::parseDateFromLdap($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function parseDateFromLdap($string)
    {
        // as: DateTime::createFromFormat("Ymdhis", $expiryDateString);
        // failed, a manual solution was chosen because of lack of time
        // feel free to update this method

        $year = substr($string, 0, 4);
        $month = substr($string, 4, 2);
        $day = substr($string, 6, 2);
        $hour = substr($string, 8, 2);
        $minute = substr($string, 10, 2);
        $second = substr($string, 12, 2);

        $dateTime = new \DateTime();
        $dateTime->setDate($year, $month, $day);
        $dateTime->setTime($hour, $minute, $second);

        return $dateTime;
    }
}
