<?php

namespace Dvsa\Mot\Behat\Support\Data\DefaultData;

use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\SiteTypeCode;

class DefaultVehicleTestingStation
{
    private static $site;

    /**
     * @return SiteDto
     */
    public static function get()
    {
        return self::$site;
    }

    /**
     * @param SiteDto $site
     */
    public static function set(SiteDto $site)
    {
        if ($site->getType() !== SiteTypeCode::VEHICLE_TESTING_STATION) {
            throw new \InvalidArgumentException(sprintf("Expected type of '%s', but got '%s'", SiteTypeCode::VEHICLE_TESTING_STATION, $site->getType()));
        }

        self::$site = $site;
    }
}
