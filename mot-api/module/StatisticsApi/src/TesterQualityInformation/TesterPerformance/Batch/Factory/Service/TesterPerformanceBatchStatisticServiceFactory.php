<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Batch\Factory\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\TesterPerformanceBatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TesterPerformanceBatchStatisticServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $storage */
        $storage = $serviceLocator->get('TqiStore');
        /** @var DateTimeHolderInterface $dateTimeHolder */
        $dateTimeHolder = $serviceLocator->get(DateTimeHolderInterface::class);
        /** @var NationalStatisticsService $nationalStatisticsService */
        $nationalStatisticsService = $serviceLocator->get(NationalStatisticsService::class);

        return new TesterPerformanceBatchStatisticsService(
            $storage,
            $dateTimeHolder,
            $nationalStatisticsService
        );
    }
}
