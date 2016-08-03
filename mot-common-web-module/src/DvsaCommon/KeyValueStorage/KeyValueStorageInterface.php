<?php

namespace DvsaCommon\KeyValueStorage;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

interface KeyValueStorageInterface
{
    /**
     * Removes an object stored under the key.
     *
     * @param $key
     */
    public function delete($key);

    /**
     * Returns a value or null if not found.
     *
     * @param $key
     * @return mixed
     */
    public function getAsJsonArray($key);

    /**
     * @param $key
     * @param $dtoClass
     * @return ReflectiveDtoInterface
     */
    public function getAsDto($key, $dtoClass);

    /**
     * @param $key
     * @param $dtoClass
     * @return ReflectiveDtoInterface[]
     */
    public function getAsDtoArray($key, $dtoClass);

    /**
     * @param $key
     * @param ReflectiveDtoInterface | ReflectiveDtoInterface[] $dto
     */
    public function storeDto($key, $dto);

    /**
     * Stores object under the key
     *
     * @param $key
     * @param $json
     */
    public function storeJsonArray($key, $json);

    /**
     * List keys in storage
     *
     * @param $keyPrefix
     * @return string[]
     */
    public function listKeys($keyPrefix = '');
}
