<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Factory\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Repository\NationalStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage;
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
        /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Storage\NationalTesterPerformanceStatisticsStorage $storage */
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
