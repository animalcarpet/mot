<?php

namespace DvsaCommonApi\Filter;

use DvsaCommon\Dto\AbstractDataTransferObject;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class XssFilter.
 */
class XssFilter implements FilterInterface
{
    private static $REMOVE_TAGS = ['script', 'style'];

    /**
     * Returns the result of filtering $value.
     *
     * @param mixed $value
     *
     * @throws \Zend\Filter\Exception\RuntimeException If filtering $value is impossible
     *
     * @return mixed
     */
    public function filter($value)
    {
        if ($value instanceof AbstractDataTransferObject) {
            return  $this->filterDto($value);
        } elseif (is_array($value)) {
            return $this->filterMultiple($value);
        } elseif (!empty($value) && is_string($value)) {
            $value = preg_replace('/(<('.implode('|', self::$REMOVE_TAGS).')\b[^>]*>).*?(<\/\2>)/is', '', $value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function filterMultiple(array $values)
    {
        foreach ($values as $key => $val) {
            $values[$key] = $this->filter($val);
        }

        return $values;
    }

    public function filterDto(AbstractDataTransferObject $dto)
    {
        $props = $this->getProperties($dto);

        foreach ($props as $prop) {
            $prop->setAccessible(true);

            $val = $prop->getValue($dto);
            if (!empty($val)) {
                $prop->setValue($dto, $this->filter($val));
            }
        }

        return $dto;
    }

    /**
     * This function read the property of each element of a class (PRIVATE/PROTECTED/PUBLIC).
     *
     * @return ReflectionProperty[]
     */
    private function getProperties($className)
    {
        $ref = new ReflectionClass($className);

        $result = array();

        $props = $ref->getProperties(
            ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC
        );

        foreach ($props as $prop) {
            $f = $prop->getName();

            $result[$f] = $prop;
        }

        if ($parentClass = $ref->getParentClass()) {
            $parentProps = $this->getProperties($parentClass->getName());

            if (count($parentProps) > 0) {
                $result = $parentProps + $result;
            }
        }

        return $result;
    }
}
