<?php

namespace TestSupport\Factory;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationLogFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $logger = new Logger();
        $writer = new Stream($config['logger']['output']);
        $logger->addWriter($writer);

        return $logger;
    }
}
