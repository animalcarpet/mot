<?php

namespace Dvsa\Mot\Behat\Support\Data\DefaultData;

use DvsaCommon\Dto\Organisation\OrganisationDto;

class DefaultAuthorisedExaminer
{
    private static $ae;

    /**
     * @return OrganisationDto
     */
    public static function get()
    {
        return self::$ae;
    }

    /**
     * @param OrganisationDto $ae
     */
    public static function set(OrganisationDto $ae)
    {
        self::$ae = $ae;
    }
}
