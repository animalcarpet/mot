<?php

namespace DvsaCommon\DtoSerialization;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;

class DtoReflectiveDeserializer implements AutoWireableInterface
{
    private $reflector;

    private $convertiblesRegister;

    public function __construct()
    {
        $this->convertiblesRegister = new DtoConvertibleTypesRegistry();
        $this->reflector = new DtoCachedReflector($this->convertiblesRegister);
    }

    /**
     * Creates objects from json data
     *
     * @param $data     array   Json in form of a PHP array
     * @param $dtoType  string  class of expected DTO
     *
     * @return mixed
     */
    public function deserialize($data, $dtoType)
    {
        if ($this->convertiblesRegister->isConvertibleType($dtoType)) {
            return $this->deserializeConvertible($data, $dtoType);
        }

        $reflection = $this->reflector->reflect($dtoType);

        $obj = $this->deserializeFromReflection($data, $reflection);

        return $obj;
    }

    public function deserializeArray(array $data, $dtoType)
    {
        $dtoArray = [];

        if ($this->convertiblesRegister->isConvertibleType($dtoType)) {
            return $this->deserializeArrayOfConvertibles($data, $dtoType);
        }

        $reflection = $this->reflector->reflect($dtoType);

        foreach ($data as $dtoData) {
            if (!is_array($dtoData)) {
                if ($dtoData == null) {
                    throw DtoDeserializationException::createNullInsteadOfDtoArrayException($dtoType);
                } elseif (is_object($dtoData)) {
                    throw DtoDeserializationException::createObjectInsteadOfDtoArrayException($dtoType, $dtoData);
                } else {
                    throw DtoDeserializationException::createScalarInsteadOfDtoArrayException($dtoType, $dtoData);
                }
            }

            $dto = $this->deserializeFromReflection($dtoData, $reflection);
            $dtoArray[] = $dto;
        }

        return $dtoArray;
    }

    private function deserializeFromReflection(array $data, DtoClassReflection $reflection)
    {
        $obj = $this->createEntity($reflection->getClass());

        foreach ($reflection->getProperties() as $property) {
            if (!array_key_exists($property->getName(), $data)) {
                throw DtoDeserializationException::createJsonMissingPropertyException($property->getName(), $reflection->getClass(), array_keys($data));
            }

            $value = $data[$property->getName()];

            if ($value !== null) {
                $setter = $property->getSetAccessor();

                if ($property->isDto()) {
                    if ($property->isArray()) {
                        $value = $this->deserializeArray($value, $property->getClass());
                    } else {
                        if (!is_array($value)) {
                            throw DtoDeserializationException::createNestedScalarInsteadOfDtoArrayException(
                                $reflection->getClass(), $property->getClass(), $property->getName(), $value
                            );
                        }

                        $value = $this->deserialize($value, $property->getClass());
                    }
                } elseif($property->isConvertible()) {
                    if ($property->isArray()) {
                        $value = $this->deserializeArrayOfConvertibles($value, $property->getClass());
                    } else {
                        $value = $this->deserializeConvertible($value, $property->getClass());
                    }
                }

                $obj->$setter($value);
            }
        }

        return $obj;
    }

    private function deserializeArrayOfConvertibles(array $convertibles, $class)
    {
        $converter = $this->convertiblesRegister->getConverter($class);

        return ArrayUtils::map($convertibles, function ($convertible) use ($converter) {
            return $converter->jsonToObject($convertible);
        });
    }

    private function deserializeConvertible($convertible, $class)
    {
        $converter = $this->convertiblesRegister->getConverter($class);

        return $converter->jsonToObject($convertible);
    }

    private function createEntity($entityClass)
    {
        return new $entityClass;
    }
}
