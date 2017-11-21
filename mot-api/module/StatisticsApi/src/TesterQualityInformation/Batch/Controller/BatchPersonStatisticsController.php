<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchPersonTestQualityInformationService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use Zend\View\Model\JsonModel;
use DvsaFeature\FeatureToggles;
use DvsaCommon\Constants\FeatureToggle as FeatureToggleConstans;

class BatchPersonStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    const QUERY_PARAM_MONTHS_AGO = 'monthsAgo';
    const DEFAULT_NUMBER_OF_MONTHS_AGO = 1;

    private $batchPersonTestQualityInformation;
    private $featureToggles;

    public function __construct(BatchPersonTestQualityInformationService $batchPersonTestQualityInformation, FeatureToggles $featureToggles)
    {
        $this->batchPersonTestQualityInformation = $batchPersonTestQualityInformation;
        $this->featureToggles = $featureToggles;
    }

    public function generateTestCountsAction()
    {
        if ($this->isStatsGenerationDisabled()) {
            return $this->returnSuccess();
        }

        $this->batchPersonTestQualityInformation->generatePersonTestStatistics($this->getNumberOfMonthsAgo());

        return $this->returnSuccess();
    }

    public function generateTestCountsForDateAction()
    {
        $year = (int) $this->params()->fromRoute("year");
        $month = (int) $this->params()->fromRoute("month");
        $day = (int) $this->params()->fromRoute("day");

        $this->batchPersonTestQualityInformation->generatePersonTestStatisticsForDate($year, $month, $day);

        return $this->returnSuccess();
    }

    public function generateRfrCountsAction()
    {
        if ($this->isStatsGenerationDisabled()) {
            return $this->returnSuccess();
        }

        $this->batchPersonTestQualityInformation->generatePersonComponentStatistics($this->getNumberOfMonthsAgo());

        return $this->returnSuccess();
    }

    public function generateRfrCountsForDateAction()
    {
        $year = (int) $this->params()->fromRoute("year");
        $month = (int) $this->params()->fromRoute("month");
        $day = (int) $this->params()->fromRoute("day");

        $this->batchPersonTestQualityInformation->generatePersonComponentStatisticsForDate($year, $month, $day);

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

    private function isStatsGenerationDisabled()
    {
        $numberOfMonthsAgo = $this->getNumberOfMonthsAgo();
        if ($this->featureToggles->isDisabled(FeatureToggleConstans::GQR_DISABLE_3_MONTHS_ENDPOINTS) === false && $numberOfMonthsAgo !== self::DEFAULT_NUMBER_OF_MONTHS_AGO) {
            return true;
        }

        return false;
    }
}
