<?php

namespace DvsaMotApi\Helper;

/**
 * Class FuzzySearchRegexHelper.
 */
class FuzzySearchRegexHelper
{
    /**
     * For string given generates a regex matching similar strings where chars might have been substituted with
     * others similar looking chars. Assumes that $string contains only alphanumeric characters.
     *
     * @param string $string             alphanumeric string
     * @param array  $similarCharMapping map char to array of it's similar looking counterparts (including itself)
     *
     * @return string regex
     */
    public static function regexForSimilarChars($string, $similarCharMapping)
    {
        $mappedStringArray = array_map(
            function ($char) use ($similarCharMapping) {
                return isset($similarCharMapping[$char]) ? ('['.implode('', $similarCharMapping[$char]).']') : $char;
            },
            str_split($string)
        );

        return implode('', $mappedStringArray);
    }

    /**
     * Given collection of similar looking char groups creates a mapping, char to array of similar looking counterparts.
     *
     * @param $charGroups array of arrays containing similar looking char groups
     *
     * @return array map char to array of similar looking chars
     */
    public static function charGroupsToMapping($charGroups)
    {
        $charMapping = [];
        foreach ($charGroups as $charGroup) {
            foreach ($charGroup as $char) {
                if (isset($charMapping[$char])) {
                    $charMapping[$char] = array_unique(array_merge($charMapping[$char], $charGroup));
                } else {
                    $charMapping[$char] = $charGroup;
                }
            }
        }

        return $charMapping;
    }

    /**
     * Uppercase character groups.
     *
     * @param $charGroups array of arrays containing similar looking char groups
     *
     * @return array uppercased char groups
     */
    public static function uppercaseCharGroups($charGroups)
    {
        return array_map(
            function ($charGroup) {
                return array_map(
                    function ($char) {
                        return strtoupper($char);
                    },
                    $charGroup
                );
            },
            $charGroups
        );
    }
}
