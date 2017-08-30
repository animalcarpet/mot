<?php

namespace TestSupport\Factory;

use DvsaCommon\UrlBuilder\UrlBuilder;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Service\StatisticsDbService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StatisticsDbServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new StatisticsDbService(
            $serviceLocator->get(TestSupportRestClientHelper::class),
            new UrlBuilder()
        );
    }

}
