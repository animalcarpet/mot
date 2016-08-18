<?php

namespace Session;

use Zend\Session\Container as BaseContainer;
use Zend\Stdlib\ArrayObject;

/**
 * Class Container
 *
 * This class exists to fix a bug in ZF2 it can be dropped once this pull request is accepted into stable:
 * https://github.com/zendframework/zf2/pull/6427
 *
 * @package Session
 */
class Container extends BaseContainer
{
    /**
     * Creates a copy of the specific container name
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $storage   = $this->verifyNamespace();
        $container = $storage[$this->getName()];

        if ($container instanceof ArrayObject) {
            return $container->getArrayCopy();
        }

        return $container;
    }
}
