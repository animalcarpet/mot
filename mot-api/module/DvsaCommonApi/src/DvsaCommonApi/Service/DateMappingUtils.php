<?php

namespace DvsaCommonApi\Service;

use DvsaCommon\Date\DateTimeApiFormat;

/**
 * Class DateMappingUtils.
 */
class DateMappingUtils
{
    public static function extractDateTimeObject($dateTimeObject)
    {
        return self::extractDateObjectInternal(
            $dateTimeObject,
            function ($dateObjectInt) {
                return DateTimeApiFormat::dateTime($dateObjectInt);
            }
        );
    }

    public static function extractDateObject($dateObject)
    {
        return self::extractDateObjectInternal(
            $dateObject,
            function ($dateObjectInt) {
                return DateTimeApiFormat::date($dateObjectInt);
            }
        );
    }

    private static function extractDateObjectInternal($dateObject, $isoMethod)
    {
        if (null !== $dateObject) {
            if ($dateObject instanceof \DateTime) {
                return $isoMethod($dateObject);
            } else {
                if (is_object($dateObject)) {
                    $type = get_class($dateObject);
                } else {
                    $type = gettype($dateObject);
                }
                throw new \InvalidArgumentException(
                    'Unexpected object class '.$type.'; expecting DateTime'
                );
            }
        }

        return null;
    }

    public static function extractDateTimeOrUnsetField(&$extractedResult, $key)
    {
        self::extractDateOrDateTimeOrUnsetField(
            $extractedResult,
            $key,
            function ($dateObject) {
                return DateMappingUtils::extractDateTimeObject($dateObject);
            }
        );
    }

    public static function extractDateOrUnsetField(&$extractedResult, $key)
    {
        self::extractDateOrDateTimeOrUnsetField(
            $extractedResult,
            $key,
            function ($dateObject) {
                return DateMappingUtils::extractDateObject($dateObject);
            }
        );
    }

    private static function extractDateOrDateTimeOrUnsetField(&$extractedResult, $key, $extractFn)
    {
        if (is_array($extractedResult) && array_key_exists($key, $extractedResult)) {
            $dateExtracted = $extractFn($extractedResult[$key]);
            if ($dateExtracted) {
                $extractedResult[$key] = $dateExtracted;
            } else {
                unset($extractedResult[$key]);
            }
        }
    }
}
