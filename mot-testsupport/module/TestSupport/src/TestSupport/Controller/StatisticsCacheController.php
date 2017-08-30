<?php

namespace TestSupport\Controller;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\MotService;
use TestSupport\Service\StatisticsAmazonCacheService;
use TestSupport\Service\StatisticsDbService;

class StatisticsCacheController extends BaseTestSupportRestfulController implements AutoWireableInterface
{
    private $amazonCache;
    private $motService;
    private $statisticsDbService;

    public function __construct(
        StatisticsAmazonCacheService $amazonCache,
        StatisticsDbService $statisticsDbService,
        MotService $motService
    )
    {
        $this->amazonCache = $amazonCache;
        $this->statisticsDbService = $statisticsDbService;
        $this->motService = $motService;
    }

    public function removeAllAmazonCacheAction()
    {
        return $this->amazonCache->removeAll();
    }

    public function removeAllDbCacheAction()
    {
        $this->motService->removeAllTestStatistics();

        return TestDataResponseHelper::jsonOk();
    }

    public function generateDbCacheAction()
    {
        $monthsAgo = $this->params()->fromRoute('monthsAgo', 1);

        $this->motService->removeAllTestStatistics();

        if ($this->statisticsDbService->createStatisticsDbRfr($monthsAgo) && $this->statisticsDbService->createStatisticsDbTests($monthsAgo)) {
            return TestDataResponseHelper::jsonOk();
        }

        return TestDataResponseHelper::jsonError('Tqi Statistics not created correctly');
    }
}
