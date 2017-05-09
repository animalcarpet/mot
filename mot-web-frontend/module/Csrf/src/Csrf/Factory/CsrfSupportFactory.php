<?php

namespace Csrf\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container as SessionContainer;
use Csrf\CsrfSupport;

class CsrfSupportFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $csrfSession = new SessionContainer('csrf');

        return new CsrfSupport(
            $csrfSession
        );
    }
}
