<?php
namespace Core\Collection;

use DvsaCommon\Utility\TypeCheck;

/**
 * Class Collection
 * @package Core\Collection
 *
 * @deprecated
 */
class Collection implements \Iterator, \Countable
{
    private $expectedInstance;
    private $data = [];

    public function __construct($expectedInstance, array $data = [])
    {
        $this->expectedInstance = $expectedInstance;
        $this->set($data);
    }

    public function set(array $data)
    {
        $this->validateData($data);
        $this->data = $data;

        return $this;
    }

    public function add($object, $key = null)
    {
        $this->validate($object);
        if ($key === null) {
            $this->data[] = $object;
        } else {
            $this->data[$key] = $object;
        }

        return $this;
    }

    public function get($key)
    {
        if ($this->containsKey($key) === false) {
            throw new \InvalidArgumentException(sprintf("'%s' not found in collection", $key));
        }

        return $this->data[$key];
    }

    public function tryGet($key)
    {
        if ($this->containsKey($key) === true) {
            $this->get($key);
        }

        return null;
    }

    public function first()
    {
        return reset($this->data);
    }

    public function last()
    {
        return end($this->data);
    }

    public function contains($element)
    {
        return in_array($element, $this->data, true);
    }

    public function containsKey($key)
    {
        return isset($this->data[$key]) || array_key_exists($key, $this->data);
    }

    public function filter(\Closure $closure)
    {
        return new static($this->expectedInstance, $this->filterData($closure));
    }

    protected function filterData(\Closure $closure)
    {
        return array_filter($this->data, $closure);
    }

    public function map(\Closure $closure)
    {
        return new static($this->expectedInstance, $this->mapData($closure));
    }

    protected function mapData(\Closure $closure)
    {
        return array_map($closure, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return false !== current($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->data);
    }

    public function toArray()
    {
        return $this->data;
    }

    private function validateData(array $data)
    {
        foreach ($data as $object) {
            $this->validate($object);
        }

        $this->data = $data;
    }

    private function validate($object)
    {
        TypeCheck::assertInstance($object, $this->expectedInstance);
    }

    public function clear()
    {
        $this->data = [];
        return $this;
    }
}
