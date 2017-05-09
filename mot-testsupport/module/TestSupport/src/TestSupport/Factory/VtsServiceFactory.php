<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\VtsService;
use Doctrine\ORM\EntityManager;
use DvsaCommon\UrlBuilder\UrlBuilder;
use TestSupport\Helper\TestSupportRestClientHelper;

class VtsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new VtsService(
            $serviceLocator->get(TestSupportRestClientHelper::class),
            $serviceLocator->get(EntityManager::class),
            new UrlBuilder()
        );

        return $service;
    }
}
