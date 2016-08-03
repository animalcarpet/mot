<?php

namespace DvsaCommon\Utility;

/**
 * A set of static methods to make working with arrays more convenient
 */
class ArrayUtils
{
    /**
     * Try to get value from an array, if it's not set - return default value (null if not set implicitly)
     *
     * @param       $array
     * @param       $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function tryGet($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Gets value from array by key. This key MUST be found in the array. OutOfBoundsException thrown otherwise
     *
     * @param array $data
     * @param string $key
     *
     * @return mixed
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public static function get($data, $key)
    {
        TypeCheck::assertArray($data);

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        throw new \OutOfBoundsException('Missing ' . $key . ' key.');
    }

    /**
     * Extracts an array subset from the array taking only the values under the specified keys
     *
     * <code>
     * $sourceArray = ['name'=>'john', 'town' => 'Bristol' , 'age' => '20'];
     * $wantedKeys = ['name', 'age'];
     *
     * $subset = ArrayUtils::pluck($sourceArray, $wantedKeys);
     * </code>
     *
     * $subset will contain ['name'=>'john', 'age' => '20']
     *
     * @param       $array
     * @param array $wantedKeys Keys to keep
     *
     * @return array
     */
    public static function valuesByKeys(array $array, array $wantedKeys)
    {
        return array_intersect_key($array, array_flip($wantedKeys));
    }

    /**
     * Finds the first element in the collection that matches the predicate.
     * If none is found than null is returned.
     *
     * @param array|Object $haystack
     * @param callable $predicate
     *
     * @return $needle
     */
    public static function firstOrNull($haystack, callable $predicate = null)
    {
        $predicate = $predicate ? : function ($element) {
            return true;
        };

        if (!empty($haystack)) {
            foreach ($haystack as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        }

        return null;
    }

    /**
     * Finds the first element in the collection that matches the predicate.
     * If none is found than exception is thrown
     *
     * @param array|Object $haystack
     * @param callable $predicate
     *
     * @return $needle
     */
    public static function first($haystack, callable $predicate = null)
    {
        $element = self::firstOrNull($haystack, $predicate);

        if ($element === null) {
            throw new \OutOfRangeException("Tried to extract first element from empty collection.");
        }

        return $element;
    }

    /**
     * Finds the last element in the collection that matches the predicate.
     * If none is found that null is returned.
     *
     * @param array|Object $haystack
     * @param callable $predicate
     *
     * @return $needle
     */
    public static function lastOrNull($haystack, callable $predicate = null)
    {
        return self::firstOrNull(array_reverse($haystack, $predicate));
    }

    /**
     * Filters the collection leaving only those elements that match the predicate.
     *
     * @param array $haystack
     * @param       $predicate
     *
     * @return array
     */
    public static function filter($haystack, callable $predicate)
    {
        $filtered = [];
        foreach ($haystack as $element) {
            if ($predicate($element)) {
                $filtered[] = $element;
            }
        }

        return $filtered;
    }

    /**
     * Provides same functionality as array_map(), but works with all iterable collections, not only arrays.
     *
     * @param $collection [] A sequence of values to invoke a transform function on.
     * @param $selector   callable A transform function to apply to each element.
     *
     * @return array
     */
    public static function map($collection, callable $selector)
    {
        $data = [];
        foreach ($collection as $key => $element) {
            $data[$key] = $selector($element);
        }

        return $data;
    }

    /**
     * Groups array elements by generated key.
     *
     * @param $collection []         Collection of elements to be grouped
     * @param callable $keyGenerator Used to generate keys to group by
     * @return array                 A map of generated key to array of grouped elements
     */
    public static function groupBy($collection, callable $keyGenerator)
    {
        $data = [];
        foreach ($collection as $element) {
            $key = $keyGenerator($element);
            $data[$key][] = $element;
        }

        return $data;
    }

    /**
     * Provides same functionality as ArrayUtils::map(), but allows also to select keys.
     *
     * @param array $collection       A sequence of values to invoke a transform function on.
     * @param callable $keySelector   A transform function to apply to each element.
     * @param callable $valueSelector A transform function to apply to each element.
     * @return array
     */
    public static function mapWithKeys($collection, callable $keySelector, callable $valueSelector)
    {
        $data = [];
        foreach ($collection as $key => $element) {
            $newKey = $keySelector ($key, $element);
            $newValue = $valueSelector ($key, $element);

            $data[$newKey] = $newValue;
        }

        return $data;
    }

    /**
     * Checks if the collection has an element that matches the predicate.
     *
     * @param $collection [] A sequence of values to search through.
     * @param $predicate  callable The predicate to find the element.
     *
     * @return bool
     */
    public static function anyMatch($collection, callable $predicate)
    {
        foreach ($collection as $element) {
            if ($predicate($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sort array of arrays based on given property in descending order
     *
     * @param $array
     * @param $propertyName
     */
    public static function sortByDesc($array, $propertyName)
    {
        usort(
            $array,
            function ($x, $y) use ($propertyName) {
                return ($x[$propertyName] === $y[$propertyName]) ? 0 : ($x[$propertyName] < $y[$propertyName]) ? 1 : -1;
            }
        );

        return $array;
    }


    /**
     * Sort array of arrays based on given property in ascending order
     *
     * @param $array
     * @param $property
     */
    public static function asortBy($array, $property = null)
    {
        uasort($array, self::getFnc($array, $property));
        return $array;
    }

    /**
     * Sort array of arrays based on given property in ascending order
     *
     * @param $array
     * @param $property
     */
    public static function sortBy($array, $property = null)
    {
        usort($array, self::getFnc($array, $property));
        return $array;
    }

    private static function getFnc($array, $property)
    {
        $isMethod = method_exists(current($array), $property);

        return function ($x, $y) use ($property, $isMethod) {
            if ($isMethod) {
                $xv = $x->$property();
                $yv = $y->$property();
            } elseif ($property !== null) {
                $xv = $x[$property];
                $yv = $y[$property];
            } else {
                $xv = $x;
                $yv = $y;
            }

            return ($xv === $yv) ? 0 : ($xv > $yv) ? 1 : -1;
        };
    }

    /**
     * Removes prefix from array keys and afterwards lowercase first letter
     *
     * @param array $data
     * @param       $prefix
     *
     * @return array
     */
    public static function removePrefixFromKeys(array $data, $prefix)
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (strpos($key, $prefix) !== false) {
                $result[lcfirst(str_replace($prefix, '', $key))] = $value;
            }
        }

        return $result;
    }

    /**
     * For all kys in array makes the first letter capital and then adds given prefix.
     * Removes from result elements with keys that did not have the prefix.
     *
     * @param array $data
     * @param       $prefix
     *
     * @return array
     */
    public static function addPrefixToKeys(array $data, $prefix)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$prefix . ucfirst($key)] = $value;
        }

        return $result;
    }

    /**
     * Check if array contains non-empty, non-zero value at given key.
     *
     * @param array $array
     * @param string $key
     *
     * @return bool
     */
    public static function hasNotEmptyValue(array $array, $key)
    {
        return isset($array[$key]) && !empty($array[$key]);
    }

    /**
     * Remove a value from an array, without knowing the key.
     *
     * @param array $array
     * @param       $value
     *
     * @return array
     */
    public static function unsetValue(array $array, $value)
    {
        if (($key = array_search($value, $array)) !== false) {
            unset($array[$key]);
        }

        return $array;
    }

    /**
     * Moves an element with the given key to the top of the array.
     * If the element with given key does not exist, then is created with null value.
     * Returns modified array.
     *
     * <code>
     * $source = ['k1'=>'v1', 'k2' => 'v2', 'k3' => 'v3'];
     * $result = moveElementToTop($source, 'k2');
     * // $result contains data as ['k2' => 'v2', 'k1'=>'v1', 'k3' => 'v3']
     * </code>
     *
     * @param $arr
     * @param $key
     *
     * @return array
     */
    public static function moveElementToTop(array $arr, $key)
    {
        if (!isset($arr[$key])) {
            $arr[$key] = null;
        }

        $valueHolder = $arr[$key];
        unset($arr[$key]);
        $arr = array_reverse($arr, true);
        $arr[$key] = $valueHolder;
        return array_reverse($arr, true);
    }

    /**
     * To check if all values on a one dimension array are null
     *
     * @param array $array
     * @return bool
     */
    public static function containsOnlyNull($array)
    {
        foreach ($array as $item) {
            if (!is_null($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return all the keys
     *
     * @param array $data
     * @return array
     */
    public static function getKeys(array $data) {
        return array_keys($data);
    }

    /**
     * Checks if array is associative or sequential
     * @param array $array
     * @return bool true if array is associative
     */
    public static function isAssoc(array $array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    public static function joinNonEmpty($glue, $pieces)
    {
        return join($glue, array_filter($pieces));
    }

    public static function aggregate($elements, $startingValue, $aggregateFunction)
    {
        $total = $startingValue;
        foreach ($elements as $element) {
            $total = $aggregateFunction($element, $total);
        }

        return $total;
    }
}
