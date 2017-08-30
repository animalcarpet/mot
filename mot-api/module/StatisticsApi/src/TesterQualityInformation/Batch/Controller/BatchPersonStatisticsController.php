<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchPersonTestQualityInformationService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use Zend\View\Model\JsonModel;

class BatchPersonStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    const QUERY_PARAM_MONTHS_AGO = 'monthsAgo';
    const DEFAULT_NUMBER_OF_MONTHS_AGO = 1;

    private $batchPersonTestQualityInformation;

    public function __construct(BatchPersonTestQualityInformationService $batchPersonTestQualityInformation)
    {
        $this->batchPersonTestQualityInformation = $batchPersonTestQualityInformation;
    }

    public function generateTestCountsAction()
    {
        $this->batchPersonTestQualityInformation->generatePersonTestStatistics($this->getNumberOfMonthsAgo());

        return $this->returnSuccess();
    }

    public function generateRfrCountsAction()
    {
        $this->batchPersonTestQualityInformation->generatePersonComponentStatistics($this->getNumberOfMonthsAgo());

        return $this->returnSuccess();
    }

    /**
     * @return int
     */
    private function getNumberOfMonthsAgo():int
    {
        return abs((int)$this->getRequest()->getQuery(self::QUERY_PARAM_MONTHS_AGO, self::DEFAULT_NUMBER_OF_MONTHS_AGO));
    }

    /**
     * @return JsonModel
     */
    private function returnSuccess():JsonModel
    {
        return ApiResponse::jsonOk(['success' => true]);
    }
}