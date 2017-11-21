<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;
    private $dateTimeHolder;

    public function __construct(NationalComponentStatisticsService $service, DateTimeHolderInterface $dateTimeHolder)
    {
        $this->service = $service;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function get($group)
    {
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $numberOfMonths = (new LastMonthsDateRange($this->dateTimeHolder))->setNumberOfMonths($monthRange);
        $reportYear = $this->dateTimeHolder->getCurrentDate()->format('Y');
        $reportMonth = $this->dateTimeHolder->getCurrentDate()->format('n');
        $dto = $this->service->get($numberOfMonths, strtoupper($group), $reportYear, $reportMonth);

        return $this->returnDto($dto);
    }
}
