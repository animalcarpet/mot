<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Factory\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Storage\SiteTesterPerformanceStatisticsStorage;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteTesterPerformanceStatisticsStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $tqiStore */
        $tqiStore = $serviceLocator->get("TqiStore");

        return new SiteTesterPerformanceStatisticsStorage(
            $tqiStore
        );
    }
}
