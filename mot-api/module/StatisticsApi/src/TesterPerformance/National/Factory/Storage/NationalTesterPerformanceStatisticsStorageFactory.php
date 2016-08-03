<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\NationalTesterPerformanceStatisticsStorage;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NationalTesterPerformanceStatisticsStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $tqiStore */
        $tqiStore = $serviceLocator->get("TqiStore");

        return new NationalTesterPerformanceStatisticsStorage(
            $tqiStore
        );
    }
}
