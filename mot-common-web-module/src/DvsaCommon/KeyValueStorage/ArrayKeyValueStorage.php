<?php

namespace DvsaCommon\KeyValueStorage;

use DvsaCommon\DtoSerialization\DtoConvertibleTypesRegistry;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\DtoSerialization\DtoReflector;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\StringUtils;

class ArrayKeyValueStorage implements KeyValueStorageInterface
{
    private $deserializer;
    private $serializer;

    protected $storedData = [];

    function __construct()
    {
        $this->deserializer = new DtoReflectiveDeserializer();
        $this->serializer = new DtoReflectiveSerializer();
    }

    /**
     * Removes an object stored under the key.
     *
     * @param $key
     */
    public function delete($key)
    {
        unset($this->storedData[$key]);
    }

    /**
     * Returns a value or null if not found.
     *
     * @param $key
     * @return mixed
     */
    public function getAsJsonArray($key)
    {
        $object = ArrayUtils::tryGet($this->storedData, $key, null);

        if ($object === null) {
            return null;
        }

        return json_decode($object, true);
    }

    /**
     * @param $key
     * @param $dtoClass
     * @return ReflectiveDtoInterface
     */
    public function getAsDto($key, $dtoClass)
    {
        $jsonArray = $this->getAsJsonArray($key);

        if ($jsonArray === null) {
            return null;
        }

        return $this->deserializer->deserialize($jsonArray, $dtoClass);
    }

    /**
     * @param $key
     * @param $dtoClass
     * @return ReflectiveDtoInterface[]
     */
    public function getAsDtoArray($key, $dtoClass)
    {
        $jsonArray = $this->getAsJsonArray($key);

        if ($jsonArray === null) {
            return null;
        }

        return $this->deserializer->deserializeArray($jsonArray, $dtoClass);
    }

    /**
     * @param $key
     * @param ReflectiveDtoInterface | ReflectiveDtoInterface[] $dto
     */
    public function storeDto($key, $dto)
    {
        $jsonArray = $this->serializer->serialize($dto);

        $this->storeJsonArray($key, $jsonArray);
    }

    /**
     * Stores object under the key
     *
     * @param $key
     * @param $json
     */
    public function storeJsonArray($key, $json)
    {
        $this->storedData[$key] = json_encode($json);
    }

    /**
     * List keys in storage
     *
     * @param $keyPrefix
     * @return string[]
     */
    public function listKeys($keyPrefix = '')
    {
        return ArrayUtils::filter(array_keys($this->storedData), function ($key) use ($keyPrefix) {
            return StringUtils::startsWith($key, $keyPrefix);
        });
    }
}
