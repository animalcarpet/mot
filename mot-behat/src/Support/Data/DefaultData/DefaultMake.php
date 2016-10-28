<?php
namespace Dvsa\Mot\Behat\Support\Data\DefaultData;

use DvsaCommon\Dto\Vehicle\MakeDto;

class DefaultMake
{
    private static $make;

    public static function set(MakeDto $make)
    {
        self::$make = $make;
    }

    /**
     * @return MakeDto
     */
    public static function get()
    {
        return self::$make;
    }
}
