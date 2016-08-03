<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\AuthorisedExaminer\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\AuthorisedExaminer\Service\AuthorisedExaminerStatisticsService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class AuthorisedExaminerStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $authorisedExaminerStatisticsService;

    const DEFAULT_ITEMS_PER_PAGE = 10;
    const DEFAULT_PAGE = 1;

    public function __construct(
        AuthorisedExaminerStatisticsService $siteStatisticsService
    )
    {
        $this->authorisedExaminerStatisticsService = $siteStatisticsService;
    }

    /**
     * @param int $aeId
     * @return \Zend\View\Model\JsonModel
     */
    public function get($aeId)
    {
        if (!$this->isFeatureEnabled(FeatureToggle::TEST_QUALITY_INFORMATION)) {
            return ApiResponse::jsonOk();
        }

        $sitePerformanceDtos = $this->authorisedExaminerStatisticsService->getListForPage(
            (int)$aeId,
            abs((int)$this->params()->fromQuery('page', static::DEFAULT_PAGE)),
            abs((int)$this->params()->fromQuery('itemsPerPage', static::DEFAULT_ITEMS_PER_PAGE))
        );

        return $this->returnDto($sitePerformanceDtos);
    }
}
