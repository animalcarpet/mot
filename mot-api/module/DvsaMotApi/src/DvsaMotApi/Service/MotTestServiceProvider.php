<?php

namespace DvsaMotApi\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestServiceProvider
{
    private $locator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @return MotTestService
     */
    public function getService()
    {
        return $this->locator->get('MotTestService');
    }
}
