<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\Factory;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchPersonTestQualityInformationService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller\BatchPersonStatisticsController;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BatchPersonStatisticsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var BatchPersonTestQualityInformationService $batchService */
        $batchService = $serviceLocator->get(BatchPersonTestQualityInformationService::class);
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new BatchPersonStatisticsController(
            $batchService,
            $featureToggles
        );
    }
}