<?php

namespace DvsaCommon\Enum;


class RfrDeficiencyCategoryCode
{
    const PRE_EU_DIRECTIVE = 'PE';
    const DANGEROUS = 'D';
    const MAJOR = 'MA';
    const MINOR = 'MI';

    /**
     * @return array
     */
    public static function getAll()
    {
        return [
            self::PRE_EU_DIRECTIVE,
            self::DANGEROUS,
            self::MAJOR,
            self::MINOR,
        ];
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}