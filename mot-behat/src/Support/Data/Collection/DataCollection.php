<?php
namespace Dvsa\Mot\Behat\Support\Data\Collection;

class DataCollection implements \Iterator, \Countable
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

    public function add($object, $key)
    {
        $this->validate($object);
        $this->data[$key] = $object;

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
        $this->checkIfDataIsNotEmpty();
        return reset($this->data);
    }

    public function last()
    {
        $this->checkIfDataIsNotEmpty();
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
        return new static($this->expectedInstance, array_filter($this->data, $closure));
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
        if (is_a($object, $this->expectedInstance) === false) {
            $this->throwException($object);
        }
    }

    private function throwException($object)
    {
        $type = gettype($object);
        if ($type === "object") {
            $type = get_class($object);
        }

        $msg = sprintf("Expected instance of '%s'. Got '%s'", $this->expectedInstance, $type);
        throw new \InvalidArgumentException($msg);
    }

    public function clear()
    {
        $this->data = [];
        return $this;
    }

    public function getExpectedInstance()
    {
        return $this->expectedInstance;
    }

    private function checkIfDataIsNotEmpty()
    {
        if(count($this->data) == 0) {
            throw new \OutOfBoundsException("Data is empty");
        }
    }

}
