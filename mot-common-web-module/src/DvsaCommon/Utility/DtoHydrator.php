<?php

namespace DvsaCommon\Utility;

use Doctrine\Common\Persistence\ObjectManager;
use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\Dto\JsonUnserializable;
use JsonSerializable;
use Zend\Stdlib\Hydrator\ClassMethods;

/**
 * Hydrates dto objects to array and back.
 *
 * Class DtoHydrator.
 */
class DtoHydrator
{
    private static $instance;

    private $classMethods;

    /**
     * DtoHydrator constructor.
     */
    public function __construct()
    {
        $this->classMethods = new ClassMethods(false);
    }

    /**
     * @return $this
     */
    public static function of()
    {
        if (self::$instance === null) {
            return new self();
        }

        return self::$instance;
    }

    /**
     * @param $dto
     *
     * @return array
     */
    public static function dtoToJson($dto)
    {
        if ($dto instanceof JsonSerializable) {
            return array_merge($dto->jsonSerialize(), ['_class' => get_class($dto)]);
        }

        return self::of()->extract($dto);
    }

    /**
     * @param mixed $json
     *
     * @return JsonUnserializable|AbstractDataTransferObject
     */
    public static function jsonToDto($json)
    {
        if (is_array($json) && isset($json['_class']) && in_array(JsonUnserializable::class, class_implements($json['_class']))) {
            /** @var JsonUnserializable $dto */
            $dto = new $json['_class']();
            $dto->jsonUnserialize($json);

            return $dto;
        }

         return self::of()->doHydration($json);
     }

    /**
     * @param mixed $dto
     *
     * @return array
     */
    public function extract($dto)
    {
        if (is_array($dto)) {
            return $this->extractArray($dto);
        }

        if (is_object($dto)) {
            if ($dto instanceof AbstractDataTransferObject) {
                return $this->extractObject($dto);
            } else {
                // This is killer for performance. Don't extract unknown objects. Always extend AbstractDataTransferObject
                return $dto;
            }
        }

        return $dto;
    }

    /**
     * @param $object
     *
     * @return array
     */
    private function extractObject($object)
    {
        $extractedData = $this->classMethods->extract($object);

        $nestedDtos = [];
        foreach ($extractedData as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $nestedDtos[$key] = $this->extract($value);
            }
        }

        $extractedData = array_merge($extractedData, $nestedDtos);

        return $extractedData;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function extractArray(array $array)
    {
        return ArrayUtils::map(
            $array, function ($element) {
                return $this->extract($element);
            }
        );
    }

    /**
     * Creates objects from json data
     *
     * @param $data mixed Json in form of a PHP array
     *
     * @return mixed
     */
    public function doHydration($data)
    {
        if (is_array($data)) {
            if (array_key_exists('_class', $data)) {
                return $this->hydrateObject($data);
            } elseif (array_key_exists('_enum', $data)) {
                return $this->hydrateEnum($data);
            } else {
                return $this->hydrateArray($data);
            }
        } else {
            return $data;
        }
    }

    private function hydrateArray($data)
    {
        $objects = [];
        foreach ($data as $key => $element) {
            $objects[$key] = $this->doHydration($element);
        }

        return $objects;
    }

    private function hydrateObject($data)
    {
        $obj = $this->createEntity($data['_class']);
        $this->classMethods->hydrate($data, $obj);
        $this->hydrateNestedEntities($data, $obj);

        return $obj;
    }

    private function hydrateEnum($data)
    {
        return $data['_enum']::fromName($data['name']);
    }

    private function createEntity($entityClass)
    {
        $fullClassName = $entityClass;
        return new $fullClassName;
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
        return 'set' . ucfirst($property);
    }
}
