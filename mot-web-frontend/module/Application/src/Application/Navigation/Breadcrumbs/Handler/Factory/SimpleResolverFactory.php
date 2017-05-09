<?php

namespace Application\Navigation\Breadcrumbs\Handler\Factory;

use Application\Navigation\Breadcrumbs\Handler\SimpleResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SimpleResolverFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        return new SimpleResolver($sl->get('viewhelpermanager')->get('url'));
    }
}
