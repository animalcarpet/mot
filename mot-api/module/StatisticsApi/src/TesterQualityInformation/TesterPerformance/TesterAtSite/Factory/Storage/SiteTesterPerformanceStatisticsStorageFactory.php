<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Factory\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Storage\SiteTesterPerformanceStatisticsStorage;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteTesterPerformanceStatisticsStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $tqiStore */
        $tqiStore = $serviceLocator->get('TqiStore');

        return new SiteTesterPerformanceStatisticsStorage(
            $tqiStore
        );
    }
}
