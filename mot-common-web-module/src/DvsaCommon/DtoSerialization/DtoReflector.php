<?php

namespace DvsaCommon\DtoSerialization;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use Zend\Code\Reflection\DocBlock\Tag\ParamTag;
use Zend\Code\Reflection\DocBlockReflection;

class DtoReflector implements DtoReflectorInterface
{
    private $convertiblesRegister;

    public function __construct(DtoConvertibleTypesRegistry $convertiblesRegister)
    {
        $this->convertiblesRegister = $convertiblesRegister;
    }

    public function reflect($dtoClass)
    {
        if (!class_exists($dtoClass)) {
            throw new \InvalidArgumentException("'$dtoClass' is not a class");
        }

        if (!is_subclass_of($dtoClass, ReflectiveDtoInterface::class)) {
            throw new \InvalidArgumentException("'$dtoClass' should implement '" . ReflectiveDtoInterface::class . "'");
        }

        $classReflection = new \ReflectionClass($dtoClass);

        $methods = $classReflection->getMethods();

        $setters = $this->extractSetters($methods);

        $this->validateSettersNumberOfParameters($setters);

        $properties = $this->extractPropertiesFromSetters($setters, $methods);

        return new DtoClassReflection($dtoClass, $properties);
    }

    /**
     * @param \ReflectionMethod[] $setters
     * @throws DtoReflectionException
     */
    private function  validateSettersNumberOfParameters($setters)
    {
        TypeCheck::assertCollectionOfClass($setters, \ReflectionMethod::class);

        foreach ($setters as $setter) {
            if ($setter->getNumberOfParameters() != 1) {
                throw DtoReflectionException::createInvalidNumberOfSetterParametersException($setter);
            }
        }
    }

    /**
     * @param \ReflectionMethod[] $methods
     * @return array
     */
    private function extractSetters($methods)
    {
        return ArrayUtils::filter($methods, function (\ReflectionMethod $method) {
            return preg_match('/^set/', $method->getName());
        });
    }

    /**
     * @param \ReflectionMethod[] $setters
     * @param \ReflectionMethod[] $allMethods
     * @return DtoPropertyReflection[]
     * @throws DtoReflectionException
     */
    private function extractPropertiesFromSetters($setters, $allMethods)
    {
        TypeCheck::assertCollectionOfClass($setters, \ReflectionMethod::class);
        TypeCheck::assertCollectionOfClass($allMethods, \ReflectionMethod::class);
        $grouped = [];

        foreach ($setters as $setter) {
            $propertyName = $this->propertyNameFromSetter($setter);
            $accessor = $this->extractAccessorsForProperty($propertyName, $setter, $allMethods);

            if (!$accessor) {
                continue;
            }

            $propertyType = $this->extractPropertyClass($setter);

            $grouped[] = new DtoPropertyReflection($propertyName, $propertyType, $accessor);
        }

        return $grouped;
    }

    private function extractPropertyClass(\ReflectionMethod $setter)
    {
        $docComment = $setter->getDocComment();
        $docComment = $docComment ?: "/***/";
        $docBlockReflection = new DocBlockReflection($docComment);

        $docClass = '';
        $type = DtoPropertyType::SCALAR_TYPE;

        /** @var ParamTag[] $docParams */
        $docParams = $docBlockReflection->getTags("param");

        if (count($docParams) > 1) {
            throw DtoReflectionException::createInvalidNumberOfSetterParametersInDocException($setter);
        }

        if ($docParams) {
            $docClass = $docParams[0]->getTypes()[0];
            if ($docClass) {
                if ($docClass[0] == '\\') {
                    $docClass = substr($docClass, 1);;
                }
            }
        }

        $constraintClassReflection = $setter->getParameters()[0]->getClass();
        $constraintClass = $constraintClassReflection ? $constraintClassReflection->getName() : '';

        if ($docClass && $constraintClass) {
            if ($docClass != $constraintClass) {
                throw DtoReflectionException::createMixedSetterTypesException($setter, $docClass, $constraintClass);
            }
        }

        $isArray = $setter->getParameters()[0]->isArray();

        if ($this->endsWith($docClass, '[]')) {
            $isArray = true;
            $docClass = substr($docClass, 0, -2);
        } elseif ($docClass && $isArray) {
            throw DtoReflectionException::createArrayInConstraintSingleInDocException($setter);
        }

        if (strtolower($docClass) == 'mixed') {
            throw DtoReflectionException::createMixedTypeUsedException($setter);
        }

        if ($docClass) {
            if (ScalarTypesList::isScalar($docClass)) {
                $docClass = ''; // we ignore scalar types

                $type = DtoPropertyType::SCALAR_TYPE;
            }

            if ($docClass) {
                if (class_exists($docClass)) {
                    if (is_subclass_of($docClass, ReflectiveDtoInterface::class)) {
                        $type = DtoPropertyType::DTO_TYPE;
                    } elseif ($this->convertiblesRegister->isConvertibleType($docClass)) {
                        $type = DtoPropertyType::CONVERTIBLE_TYPE;
                    } else {
                        throw DtoReflectionException::createNotDtoDocTypeException($setter, $docClass);
                    }
                } else {
                    throw DtoReflectionException::createInvalidDocTypeException($setter, $docClass);
                }
            }
        }

        if ($constraintClass) {
            if (is_subclass_of($constraintClass, ReflectiveDtoInterface::class)) {
                $type = DtoPropertyType::DTO_TYPE;
            } elseif ($this->convertiblesRegister->isConvertibleType($constraintClass)) {
                $type = DtoPropertyType::CONVERTIBLE_TYPE;
            } else {
                throw DtoReflectionException::createInvalidConstrainTypeException($setter, $constraintClass);
            }
        }

        $class = $constraintClass ? $constraintClass : $docClass;

        $propertyType = new DtoPropertyType($class, $type, $isArray);

        return $propertyType;
    }

    private function endsWith($haystack, $needle)
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    private function propertyNameFromSetter(\ReflectionMethod $setter)
    {
        return lcfirst(substr($setter->getName(), 3));
    }

    /**
     * @param $propertyName
     * @param \ReflectionMethod $setter
     * @param \ReflectionMethod[] $allMethodReflections
     * @return string
     * @throws DtoReflectionException
     */
    private function extractAccessorsForProperty($propertyName, \ReflectionMethod $setter, $allMethodReflections)
    {
        TypeCheck::assertCollectionOfClass($allMethodReflections, \ReflectionMethod::class);
        $accessors = [];

        foreach ($this->listPossibleRetrieveAccesorsForPropertyName($propertyName) as $accessor) {
            if (ArrayUtils::anyMatch($allMethodReflections, function (\ReflectionMethod $methodReflections) use ($accessor) {
                return $accessor == $methodReflections->getName();
            })
            ) {
                $accessors[] = $accessor;
            }
        }

        if (count($accessors) == 0) {
            return null;
        }

        if (count($accessors) > 1) {
            throw DtoReflectionException::createTooManyAccesorsDtoReflectionException($setter->getDeclaringClass()->getName(), $propertyName, $accessors);
        }

        return $accessors[0];
    }

    private function listPossibleRetrieveAccesorsForPropertyName($property)
    {
        yield 'get' . ucfirst($property);
        yield 'is' . ucfirst($property);
        yield 'has' . ucfirst($property);
    }
}
