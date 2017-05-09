<?php

namespace Application\Helper;

/**
 * Class DataMappingHelper.
 *
 * @see \ApplicationTest\Helper\DataMappingHelperTest
 */
class DataMappingHelper
{
    /**
     * @var array
     */
    private $haystack;

    /**
     * @var string
     */
    private $needleKey;

    /**
     * @var mixed
     */
    private $needleValue;

    /**
     * @var array
     */
    private $returnKeys = [];

    /**
     * @param array  $haystack
     * @param string $needleKey
     * @param mixed  $needleValue
     */
    public function __construct(array $haystack, $needleKey, $needleValue)
    {
        $this->haystack = $haystack;
        $this->needleKey = $needleKey;
        $this->needleValue = $needleValue;
    }

    /**
     * @param array $returnKeys The keys you want to return, default = return all keys
     *
     * @return $this
     */
    public function setReturnKeys(array $returnKeys)
    {
        $this->returnKeys = $returnKeys;

        return $this;
    }

    /**
     * Given an array of arrays it searches and finds the first occurence of needleValue present in needleKey
     * and optionally returns the keys you ask for.
     *
     * @throws \Exception              if needleValue is not found
     * @throws \BadMethodCallException if haystack is incorrect data structure
     */
    public function getValue()
    {
        $returnData = null;

        foreach ($this->haystack as $item) {
            if (!is_array($item)) {
                throw new \BadMethodCallException('Input data must be an array of arrays');
            }

            if (!array_key_exists($this->needleKey, $item)) {
                throw new \BadMethodCallException($this->needleKey.' Key is missing');
            }

            // Data does not match, next...!
            if ($item[$this->needleKey] !== $this->needleValue) {
                continue;
            }

            if (empty($this->returnKeys)) {
                $returnData = $item;
            } else {
                $returnData = $this->getReturnDataFromKeys($item);
            }

            // Found the item, quit out
            if (isset($returnData)) {
                break;
            }
        }

        if (!isset($returnData)) {
            throw new \Exception('Unable to find what you were looking for');
        }

        return $returnData;
    }

    /**
     * Returns the data requested using the keys provided.
     *
     * @param array $item
     *
     * @return array
     */
    private function getReturnDataFromKeys(array $item)
    {
        $keys = array_flip($this->returnKeys);

        return array_intersect_key($item, $keys);
    }
}
