<?php

namespace DvsaAuthentication\Identity\OpenAM\Utils;

class IdentityAttributeFinder
{
    /**
     * Searches for attribute inside identity attributes map.
     *
     * @param string $attribute
     * @param array  $map
     *
     * @return string|null attribute value or null if the value was not found
     */
    public static function find($attribute, $map)
    {
        if (count($map) === 0) {
            return null;
        }

        foreach ($map as $key => $val) {
            if (strtolower($key) === strtolower($attribute)) {
                return $val;
            }
        }

        return null;
    }
}
