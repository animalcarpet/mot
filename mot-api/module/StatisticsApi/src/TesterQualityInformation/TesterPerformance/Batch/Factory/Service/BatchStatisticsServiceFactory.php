<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Batch\Factory\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\KeyValueStorage\KeyValueStorageInterface;
use DvsaFeature\FeatureToggles;
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
        /** @var NationalComponentStatisticsService $nationalComponentService */
        $nationalComponentService = $serviceLocator->get(NationalComponentStatisticsService::class);
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new BatchStatisticsService(
            $storage,
            $dateTimeHolder,
            $nationalComponentService,
            $featureToggles
        );
    }
}
