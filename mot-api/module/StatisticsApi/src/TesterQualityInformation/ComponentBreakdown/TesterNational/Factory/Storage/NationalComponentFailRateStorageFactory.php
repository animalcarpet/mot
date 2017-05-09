<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Factory\Storage;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NationalComponentFailRateStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $tqiStore */
        $tqiStore = $serviceLocator->get('TqiStore');

        return new NationalComponentFailRateStorage(
            $tqiStore
        );
    }
}
