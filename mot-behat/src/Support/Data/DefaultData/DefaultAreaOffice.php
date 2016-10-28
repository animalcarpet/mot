<?php

namespace Dvsa\Mot\Behat\Support\Data\DefaultData;

use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\SiteTypeCode;

class DefaultAreaOffice
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
        if ($site->getType() !== SiteTypeCode::AREA_OFFICE) {
            throw new \InvalidArgumentException(sprintf("Expected type of '%s', but got '%s'", SiteTypeCode::AREA_OFFICE, $site->getType()));
        }

        self::$site = $site;
    }
}
