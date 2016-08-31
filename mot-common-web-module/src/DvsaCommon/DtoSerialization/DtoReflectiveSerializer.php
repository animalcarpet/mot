<?php

namespace DvsaCommon\DtoSerialization;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;

class DtoReflectiveSerializer implements AutoWireableInterface
{
    private $reflector;

    private $convertiblesRegister;

    public function __construct()
    {
        $this->convertiblesRegister = new DtoConvertibleTypesRegistry();
        $this->reflector = new DtoCachedReflector($this->convertiblesRegister);
    }

    public function  serialize($dto)
    {
        if (is_array($dto)) {
            $this->validateIfAllOfSameClassAndDtoOrConvertible($dto);

            if ($dto) {
                $dtoClass = get_class($dto[0]);
                if ($this->convertiblesRegister->isConvertibleType($dtoClass)) {
                    return $this->serializeConvertiblesArray($dto, $dtoClass);
                } else {
                    $reflection = $this->reflector->reflect($dtoClass);

                    return $this->serializeDtoArray($dto, $reflection);
                }
            }
            else {
                return [];
            }
        }

        if (is_object($dto)) {
            $dtoClass = get_class($dto);

            if ($dto instanceof ReflectiveDtoInterface) {
                $reflection = $this->reflector->reflect($dtoClass);

                return $this->serializeObject($dto, $reflection);
            } elseif ($this->convertiblesRegister->isConvertibleType($dtoClass)) {
                return $this->serializeConvertible($dto, $dtoClass);
            } else{
                throw DtoSerializationException::createCannotSerializeNonDtosException($dto);
            }
        }

        if ($dto === null) {
            throw DtoSerializationException::createCannotSerializeNullException();
        }

        throw DtoSerializationException::createCannotSerializeScalarValuesException($dto);
    }

    private function serializeConvertible($convertible, $expectedClass)
    {
        $converter = $this->convertiblesRegister->getConverter($expectedClass);

        return $converter->objectToJson($convertible);
    }

    private function serializeDtoArray(array $dtoArray, DtoClassReflection $reflection)
    {
        return ArrayUtils::map(
            $dtoArray, function ($element) use ($reflection) {

            if ($element == null) {
                throw DtoSerializationException::createNestedArrayOfDtosHadNullException($reflection->getClass());
            }

            if (!is_object($element)) {
                throw DtoSerializationException::createNestedArrayOfDtosHadScalarValueException(
                    $reflection->getClass(),
                    $element
                );
            }

            if (get_class($element) != $reflection->getClass()) {
                throw DtoSerializationException::createNestedArrayOfDtosHadDifferentObjectException(
                    $reflection->getClass(),
                    get_class($element)
                );
            }

            return $this->serialize($element);
        });
    }

    private function serializeConvertiblesArray(array $dtoArray, $expectedClass) {
        return ArrayUtils::map(
            $dtoArray, function ($element) use ($expectedClass) {

            return $this->serializeConvertible($element, $expectedClass);
        });
    }

    private function validateIfAllOfSameClassAndDtoOrConvertible(array $dtoArray)
    {
        if (!$dtoArray) {
            return;
        }

        $scalarValue =  ArrayUtils::firstOrNull($dtoArray, function ($element) {
            return !is_object($element);
        });

        if ($scalarValue !== null) {
            throw DtoSerializationException::createCannotSerializeArrayWithAScalarValueException($scalarValue);
        }

        if(ArrayUtils::anyMatch($dtoArray, function ($element) {
            return $element === null;
        })){
            throw DtoSerializationException::createCannotSerializeArrayWithANullValueException($scalarValue);
        }

        $classOne = get_class($dtoArray[0]);

        $objectWithDifferentClass = ArrayUtils::firstOrNull($dtoArray, function ($element) use ($classOne) {
            return get_class($element) != $classOne;
        });

        if ($objectWithDifferentClass == null) {
            return;
        }

        throw DtoSerializationException::createMixedTypesInArrayException($classOne, get_class());
    }

    private function serializeObject($object, DtoClassReflection $reflection)
    {
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $getter = $property->getRetrieveAccessor();
            $value = $object->$getter();
            $data[$property->getName()] = $this->serializeProperty($property, $value);
        }

        return $data;
    }

    private function serializeProperty(DtoPropertyReflection $property, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($property->isDto()) {
            if ($property->isArray()) {
                return $this->serializeDtoArray($value, $this->reflector->reflect($property->getClass()));
            } else {
                $reflection = $this->reflector->reflect($property->getClass());

                return $this->serializeObject($value, $reflection);
            }
        } elseif ($property->isConvertible()) {
            if ($property->isArray()){
                return $this->serializeConvertiblesArray($value, $property->getClass());
            } else {
                return $this->serializeConvertible($value, $property->getClass());
            }
        } else {
            if ($property->isArray()) {
                // todo check if value is array
                return $value;
            } else {
                // todo check if value is scalar
                return $value;
            }
        }
    }
}
