<?php

namespace DvsaMotTest\Helper;

use Zend\Session\Container as SessionContainer;

/**
 * Class LocationSelectContainerHelper.
 */
class LocationSelectContainerHelper
{
    /** @var SessionContainer $container */
    protected $container;
    protected $containerKey = 'locationSelectData';

    public function __construct(SessionContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $data
     */
    public function persistConfig(array $data)
    {
        $this->container->offsetSet($this->containerKey, $data);
    }

    public function fetchConfig()
    {
        return $this->container->offsetGet($this->containerKey);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function clearConfig()
    {
        $this->container->offsetUnset($this->containerKey);
    }
}
