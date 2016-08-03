<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\NationalComponentFailRateStorage;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NationalComponentFailRateStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $tqiStore */
        $tqiStore = $serviceLocator->get("TqiStore");

        return new NationalComponentFailRateStorage(
            $tqiStore
        );
    }
}
