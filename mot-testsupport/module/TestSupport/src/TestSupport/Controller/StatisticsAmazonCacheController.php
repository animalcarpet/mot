<?php

namespace TestSupport\Controller;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use TestSupport\Service\StatisticsAmazonCacheService;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

class StatisticsAmazonCacheController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $data including "diff" string to differentiate scheme management users
     * @return JsonModel username of new AO1 user
     */
    public function removeAllAction()
    {
        /** @var $statisticsAmazonCacheService StatisticsAmazonCacheService */
        $statisticsAmazonCacheService = $this->getServiceLocator()->get(StatisticsAmazonCacheService::class);

        $resultJson =  $statisticsAmazonCacheService->removeAll();
        return $resultJson;
    }
}
