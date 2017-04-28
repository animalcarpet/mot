<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;


use Dvsa\Mot\Behat\Support\Data\Model\Catalog;
use DvsaCommon\Utility\ArrayUtils;

abstract class AbstractCatalogMap
{
    const FIELD_ID = "id";
    const FIELD_NAME = "name";

    private $collection;
    private $catalogType;

    function __construct($catalogType)
    {
        $this->collection = Catalog::get($catalogType);
        $this->catalogType = $catalogType;
    }

    /**
     * @param int $id
     * @return string
     */
    public function getNameById($id)
    {
        return $this->filterCollection(self::FIELD_ID, $id)[self::FIELD_NAME];
    }

    /**
     * @param string $name
     * @return int
     */
    public function getIdByName($name)
    {
        return $this->filterCollection(self::FIELD_NAME, $name)[self::FIELD_ID];
    }

    /**
     * @param mixed $value
     * @param string $key
     * @return mixed
     */
    protected function filterCollection($key, $value)
    {
        $filteredData = ArrayUtils::filter($this->collection, function ($type) use ($key, $value) {
            return $type[$key] === $value;
        });

        $type = array_shift($filteredData);

        if (is_array($type) === false) {
            throw new \InvalidArgumentException("Catalog type '" . $this->catalogType . "' not found");
        }

        return $type;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }
}