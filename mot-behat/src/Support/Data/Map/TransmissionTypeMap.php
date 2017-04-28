<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;

use Dvsa\Mot\Behat\Support\Data\Model\Catalog;

class TransmissionTypeMap extends AbstractCatalogMap
{
    public function __construct()
    {
        parent::__construct(Catalog::TRANSMISSION_TYPE);
    }

    /**
     * @return int
     */
    public function getAutomaticTypeId()
    {
        return $this->getIdByName("Automatic");
    }

    /**
     * @return int
     */
    public function getManualTypeId()
    {
        return $this->getIdByName("Manual");
    }
}
