<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;

use Dvsa\Mot\Behat\Support\Data\Model\Catalog;

class ColourMap extends AbstractCatalogMap
{
    const FIELD_CODE = "code";

    public function __construct()
    {
        parent::__construct(Catalog::COLOURS);
    }

    /**
     * @param string $code
     * @return string
     */
    public function getNameByCode($code)
    {
        return parent::filterCollection(self::FIELD_CODE, $code)[self::FIELD_NAME];
    }
}
