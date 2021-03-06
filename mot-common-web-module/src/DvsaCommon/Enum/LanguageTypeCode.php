<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'language_type' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class LanguageTypeCode
{
    const ENGLISH = 'EN';
    const WELSH = 'CY';

    /**
     * @return array of values for the type LanguageTypeCode
     */
    public static function getAll()
    {
        return [
            self::ENGLISH,
            self::WELSH,
        ];
    }

    /**
     * @param mixed $key a candidate LanguageTypeCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
