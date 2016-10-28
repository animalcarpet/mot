<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\DefaultData\Params\DefaultTransmissionParams;
use Dvsa\Mot\Behat\Support\Data\Model\Catalog;
use DvsaCommon\Dto\Security\RoleDto;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Utility\ArrayUtils;

class TransmissionTypeMap
{
    const FIELD_ID = "id";
    const FIELD_NAME = "name";

    private $collection;

    public function __construct()
    {
        $this->collection = Catalog::get(Catalog::TRANSMISSION_TYPE);
    }

    /**
     * @return string
     */
    public function getNameById($id)
    {
        $filteredData = ArrayUtils::filter($this->collection, function ($type) use ($id) {
            return $type[self::FIELD_ID] === $id;
        });

        $type = array_shift($filteredData);

        if (is_array($type) === false) {
            throw new \InvalidArgumentException("Transmission Type not found");
        }

        return $type[self::FIELD_NAME];
    }

    /**
     * @return int
     */
    public function getIdByName($name)
    {
        $filteredData = ArrayUtils::filter($this->collection, function ($type) use ($name) {
            return $type[self::FIELD_NAME] === $name;
        });

        $type = array_shift($filteredData);

        if (is_array($type) === false) {
            throw new \InvalidArgumentException("Transmission Type not found");
        }

        return $type[self::FIELD_ID];
    }

    public function getAutomaticTypeId()
    {
        return $this->getIdByName("Automatic");
    }

    public function getManualTypeId()
    {
        return $this->getIdByName("Manual");
    }
}
