<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;

use Dvsa\Mot\Behat\Support\Data\Model\Catalog;

class CountryOfRegistrationMap extends AbstractCatalogMap
{
    public function __construct()
    {
        parent::__construct(Catalog::COUNTRY_OF_REGISTRATION);
    }
}
