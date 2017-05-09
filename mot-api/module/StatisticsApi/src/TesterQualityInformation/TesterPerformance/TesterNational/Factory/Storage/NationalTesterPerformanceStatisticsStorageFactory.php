<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Factory\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NationalTesterPerformanceStatisticsStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $tqiStore */
        $tqiStore = $serviceLocator->get('TqiStore');

        return new NationalTesterPerformanceStatisticsStorage(
            $tqiStore
        );
    }
}
