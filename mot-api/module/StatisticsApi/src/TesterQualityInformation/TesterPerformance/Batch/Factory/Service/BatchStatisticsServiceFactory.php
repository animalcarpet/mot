<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Batch\Factory\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BatchStatisticsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var KeyValueStorageInterface $storage */
        $storage = $serviceLocator->get('TqiStore');
        /** @var DateTimeHolderInterface $dateTimeHolder */
        $dateTimeHolder = $serviceLocator->get(DateTimeHolderInterface::class);
        /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService $nationalStatisticsService */
        $nationalStatisticsService = $serviceLocator->get(NationalStatisticsService::class);
        /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService $nationalComponentService */
        $nationalComponentService = $serviceLocator->get(NationalComponentStatisticsService::class);

        return new BatchStatisticsService(
            $storage,
            $dateTimeHolder,
            $nationalStatisticsService,
            $nationalComponentService
        );
    }
}
