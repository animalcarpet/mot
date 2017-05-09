<?php

namespace DvsaEntities\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use DvsaCommon\Date\Time;

/**
 * Class TimeType controls time between database and PHP usable formats.
 */
class TimeType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'Time';
    }

    public function getName()
    {
        return 'Time';
    }

    /**
     * @param Time             $value
     * @param AbstractPlatform $platform
     *
     * @return mixed|string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value != null ? $value->toIso8601() : null;
    }

    /**
     * @param string           $value    iso 8601
     * @param AbstractPlatform $platform
     *
     * @return int|mixed
     */
    public function convertToPhpValue($value, AbstractPlatform $platform)
    {
        return $value != null ? Time::fromIso8601($value) : null;
    }
}
