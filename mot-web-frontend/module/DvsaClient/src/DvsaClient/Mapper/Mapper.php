<?php

namespace DvsaClient\Mapper;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use Zend\Http\Client;
use Zend\Stdlib\Hydrator\ClassMethods;

/**
 * Class Mapper.
 */
abstract class Mapper
{
    /**
     * @var HttpRestJsonClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $objectClassPath;

    public function __construct(HttpRestJsonClient $client)
    {
        $this->client = $client;
    }

    public function fetchAll()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @return ClassMethods
     */
    public function getHydrator()
    {
        if (isset($this->hydrator)) {
            return $this->hydrator;
        } else {
            $this->hydrator = new ClassMethods();

            return $this->hydrator;
        }
    }

    public function hydrateArrayOfEntities($nestedArrays)
    {
        $stack = [];
        foreach ($nestedArrays as $array) {
            $obj = $this->doHydration($array);

            $stack[] = $obj;
        }

        return $stack;
    }

    public function doHydration($data)
    {
        $obj = $this->createEntity();
        $this->getHydrator()->hydrate($data, $obj);

        $params = $this->createParams($data);
        $obj = $this->hydrateNestedEntities($data, $obj, $params);

        return $obj;
    }

    /**
     * Hydrates and sets any children entities nested within the entity.
     *
     * Returns the unaltered parent entity if this method is not extended by the custom mapper.
     *
     * @param array $data
     * @param       $obj
     * @param array $params
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function hydrateNestedEntities($data, $obj, $params)
    {
        return $obj;
    }

    /**
     * Creates a params array to pass to hydrateNestedEntities.
     * Returns empty array if this method is not extended by the custom mapper.
     *
     * @param $data array to be hydrated
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createParams($data)
    {
        return [];
    }

    public function createEntity()
    {
        return new $this->entityClass();
    }

    public function getPaginationUrlString($offset, $limit)
    {
        return '?offset='.$offset.'&limit='.$limit;
    }
}
