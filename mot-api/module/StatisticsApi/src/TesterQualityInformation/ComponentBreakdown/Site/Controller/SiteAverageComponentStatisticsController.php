<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Service\SiteAverageComponentStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use Zend\View\Model\JsonModel;

class SiteAverageComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(SiteAverageComponentStatisticsService $componentStatisticsService)
    {
        $this->service = $componentStatisticsService;
        $this->setIdentifierName('siteId');
    }

    /**
     * @param int $siteId
     * @return JsonModel
     */
    public function get($siteId) : JsonModel
    {
        $group = $this->params()->fromRoute('group');
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $siteAverageStatisticsDto = $this->service->get($siteId, $group, $monthRange);

        return $this->returnDto($siteAverageStatisticsDto);
    }
}
