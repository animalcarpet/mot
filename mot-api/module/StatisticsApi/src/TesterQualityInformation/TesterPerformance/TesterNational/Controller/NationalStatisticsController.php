<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $nationalStatisticsService;
    private $dateTimeHolder;

    public function __construct(
        NationalStatisticsService $nationalStatisticsService, DateTimeHolderInterface $dateTimeHolder
    )
    {
        $this->nationalStatisticsService = $nationalStatisticsService;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function getList()
    {
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $monthRange = (new LastMonthsDateRange($this->dateTimeHolder))->setNumberOfMonths($monthRange);
        $reportYear = $this->dateTimeHolder->getCurrentDate()->format('Y');
        $reportMonth = $this->dateTimeHolder->getCurrentDate()->format('n');

        $dto = $this->nationalStatisticsService->get($monthRange, $reportYear, $reportMonth);

        return $this->returnDto($dto);
    }
}
