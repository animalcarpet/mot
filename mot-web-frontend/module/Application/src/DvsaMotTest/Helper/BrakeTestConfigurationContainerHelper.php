<?php

namespace DvsaMotTest\Helper;

use Zend\Session\Container as SessionContainer;

/**
 * Class BrakeTestConfigurationContainerHelper.
 */
class BrakeTestConfigurationContainerHelper
{
    /** @var SessionContainer $container */
    protected $container;
    protected $containerKey = 'brakeTestConfigurationData';

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
}
