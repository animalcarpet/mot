<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Factory\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Repository\NationalStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\NationalStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage\NationalTesterPerformanceStatisticsStorage;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\TimeSpan;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NationalStatisticsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NationalStatisticsRepository $repository */
        $repository = $serviceLocator->get(NationalStatisticsRepository::class);
        /** @var NationalTesterPerformanceStatisticsStorage $storage */
        $storage = $serviceLocator->get(NationalTesterPerformanceStatisticsStorage::class);
        /** @var DateTimeHolderInterface $dateTimeHolder */
        $dateTimeHolder = $serviceLocator->get(DateTimeHolderInterface::class);

        return new NationalStatisticsService(
            $repository,
            $storage,
            $dateTimeHolder,
            new TimeSpan(0, 1, 0, 0)
        );
    }
}
