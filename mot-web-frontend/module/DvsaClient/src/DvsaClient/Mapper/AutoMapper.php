<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Utility\Hydrator;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

/**
 * Class AutoMapper.
 */
class AutoMapper
{
    /**
     * @var HttpRestJsonClient
     */
    protected $client;

    /**
     * @var Hydrator
     */
    private $hydrator;

    private $entityNamespace = 'DvsaClient\\Entity\\';

    public function __construct(HttpRestJsonClient $client)
    {
        $this->client = $client;
        $this->hydrator = new Hydrator();
    }

    public function doHydration($data)
    {
        if (array_key_exists('_clazz', $data)) {
            return $this->hydrateEntity($data);
        } else {
            return $this->hydrateArray($data);
        }
    }

    private function hydrateArray($data)
    {
        $objects = [];
        foreach ($data as $element) {
            $objects[] = $this->doHydration($element);
        }

        return $objects;
    }

    private function hydrateEntity($data)
    {
        $obj = $this->createEntity($data['_clazz']);
        $this->hydrator->hydrate($data, $obj);

        $this->hydrateNestedEntities($data, $obj);

        return $obj;
    }

    private function createEntity($entityClass)
    {
        $fullClassName = $this->entityNamespace.$entityClass;

        return new $fullClassName();
    }

    private function hydrateNestedEntities(array $data, $obj)
    {
        foreach ($this->listNestedProperties($data, $obj) as $nestedProperty) {
            $setter = $this->getSetterForProperty($nestedProperty);
            $nestedEntity = $this->doHydration($data[$nestedProperty]);
            $obj->$setter($nestedEntity);
        }
    }

    /**
     * @param array $data
     * @param       $obj
     *
     * @return array
     */
    private function listNestedProperties(array $data, $obj)
    {
        $nestedKeys = [];
        foreach ($data as $property => $value) {
            if (is_array($value) && $this->setterExists($obj, $property)) {
                $nestedKeys[] = $property;
            }
        }

        return $nestedKeys;
    }

    private function setterExists($obj, $property)
    {
        $method = $this->getSetterForProperty($property);

        return is_callable([$obj, $method]);
    }

    private function getSetterForProperty($property)
    {
        return 'set'.ucfirst($property);
    }
}
