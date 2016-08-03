<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\BatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\NationalComponentStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\NationalStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BatchStatisticsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $storage */
        $storage = $serviceLocator->get("TqiStore");
        /** @var DateTimeHolderInterface $dateTimeHolder */
        $dateTimeHolder = $serviceLocator->get(DateTimeHolderInterface::class);
        /** @var NationalStatisticsService $nationalStatisticsService */
        $nationalStatisticsService = $serviceLocator->get(NationalStatisticsService::class);
        /** @var NationalComponentStatisticsService $nationalComponentService */
        $nationalComponentService = $serviceLocator->get(NationalComponentStatisticsService::class);

        return new BatchStatisticsService(
            $storage,
            $dateTimeHolder,
            $nationalStatisticsService,
            $nationalComponentService
        );
    }

}
