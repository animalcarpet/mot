<?php

namespace TestSupport\Controller;

use TestSupport\Service\StatisticsAmazonCacheService;
use Zend\View\Model\JsonModel;

class StatisticsAmazonCacheController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $data including "diff" string to differentiate scheme management users
     *
     * @return JsonModel username of new AO1 user
     */
    public function removeAllAction()
    {
        /** @var $statisticsAmazonCacheService StatisticsAmazonCacheService */
        $statisticsAmazonCacheService = $this->getServiceLocator()->get(StatisticsAmazonCacheService::class);

        $resultJson = $statisticsAmazonCacheService->removeAll();

        return $resultJson;
    }
}
