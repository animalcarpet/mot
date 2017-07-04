<?php

namespace DvsaCommon\Utility;

class TypeCheck
{
    /**
     * Return true is the arg is a positive int; false otherwise.
     *
     * @param mixed $number
     *
     * @return bool
     */
    public static function isPositiveInteger($number)
    {
        if (!self::isInteger($number)) {
            return false;
        }

        if ((int)$number >= 0) {
            return true;
        }

        return false;
    }

    public static function assertIsPositiveInteger($number)
    {
        if (!self::isPositiveInteger($number)) {
            throw new \InvalidArgumentException("Positive integer expected");
        }
    }

    public static function assertInteger($number)
    {
        if (!self::isPositiveInteger($number)) {
            throw new \InvalidArgumentException("Integer expected");
        }
    }

    /**
     * Throws \RuntimeException when passed object (1st arg) is not instance of given class (2nd arg)
     *
     * @param $object
     * @param string $class
     *
     * @throws \RuntimeException
     */
    public static function assertInstance($object, $class)
    {
        if (false === ($object instanceof $class)) {
            if ($object === null) {
                $given = 'Null';
            } elseif (is_object($object)) {
                $given = get_class($object);
            } elseif (is_callable($object)) {
                $given = 'Callable';
            } else {
                $given = 'Scalar value';
            }

            throw new \RuntimeException('Expected ' . $class . ' object. ' . $given . ' given.');
        }
    }

    /**
     * Return true is the arg represents integer (might be string, but with integer value)
     *
     * @param mixed $number
     *
     * @return bool
     */
    public static function isInteger($number)
    {
        if (!is_numeric($number)) {
            return false;
        }

        if (is_string($number) && !preg_match("/^-?[0-9]*$/", $number)) {
            return false;
        }

        if ((string)(int)$number != $number) {
            return false;
        }

        return true;
    }

    /**
     * Return true if the arg is a string; false otherwise.
     *
     * @param mixed $string
     *
     * @return bool
     */
    public static function isAlphaNumeric($string)
    {
        if (preg_match('/[^a-zA-Z0-9]+/', $string)) {
            return false;
        }
        if (strlen($string) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Verifies type of passed argument. Throws \InvalidArgumentException if not an array
     *
     * Please use this method to make sure an array is passed to method/function.
     * Do not rely on keyword 'array' in function as it cause an error instead of exception.
     * Exception is much better because it has stack trace and more useful information.
     *
     * @param mixed $data
     */
    public static function assertArray($data)
    {
        if (false === is_array($data)) {
            throw new \InvalidArgumentException('Expected array, ' . gettype($data) . ' given.');
        }
    }

    public static function assertCollectionOfClass($data, $className)
    {
        if (!self::isCollectionOfClass($data, $className)) {
            throw new \InvalidArgumentException("Expected collection of '" . $className . "'.");
        }
    }

    public static function isCollectionOfClass($data, $className)
    {
        return ArrayUtils::anyMatch(
            $data,
            function ($element) use ($className) {
                return !is_object($element) || get_class($element) != $className;
            }) === false;
    }

    public static function assertEnum($value, $enumClass)
    {
        if(!$enumClass::exists($value)) {
            throw new \InvalidArgumentException("$value is not an enum");
        }
    }

    public static function assertCollectionOfScalarValues($collection)
    {
        foreach ($collection as $element) {
            if ($element === null) {
                throw new \InvalidArgumentException("Expected collection of scalar values. Got null");
            }

            if (is_object($element)) {
                throw new \InvalidArgumentException("Expected collection of scalar values."
                    . "Got object of class '" . get_class($element) . "'"
                );
            }

            if (is_array($element)) {
                throw new \InvalidArgumentException("Expected collection of scalar values. Got array.");
            }
        }
    }

    public static function assertInArray($argument, $allowedValues)
    {
        if (!in_array($argument, $allowedValues)) {
            throw new \InvalidArgumentException('Unexpected argument value');
        }
    }

    public static function assertString($value)
    {
        if(!is_string($value)) {
            throw new \InvalidArgumentException('Expected string');
        }
    }
}
