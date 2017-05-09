<?php

namespace DvsaCommonApi\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommonApi\Logger\ZendSqlLogger;

class SqlLoggerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ZendSqlLogger(
            $serviceLocator->get('Application\Logger')
        );
    }
}
