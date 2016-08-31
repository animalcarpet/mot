<?php

namespace Dvsa\Mot\Api\StatisticsApi\ReportGeneration;

use DvsaCommon\ApiClient\Statistics\Common\ReportDtoInterface;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\TimeSpan;

abstract class AbstractReportGenerator
{
    /** @var DateTimeHolderInterface */
    private $dateTimeHolder;
    /** @var TimeSpan */
    private $timeoutPeriod;

    protected function __construct(
        DateTimeHolderInterface $dateTimeHolder,
        TimeSpan $timeoutPeriod
    )
    {
        $this->dateTimeHolder = $dateTimeHolder;
        $this->timeoutPeriod = $timeoutPeriod;
    }

    /**
     * @return ReportDtoInterface
     */
    abstract protected function getFromStorage();

    abstract protected function storeReport($report);

    private final function createInProgressReport($startedDate, $timeoutDate)
    {
        $inProgressReport = $this->createEmptyReport();
        $inProgressReport->getReportStatus()->setIsCompleted(false);
        $inProgressReport->getReportStatus()->setGenerationTimeoutDate($timeoutDate);
        $inProgressReport->getReportStatus()->setGenerationStartTime($startedDate);

        return $inProgressReport;
    }

    /**
     * @return ReportDtoInterface
     *
     */
    final private function generateReportAndStoreIt()
    {
        $timeoutDate = $this->timeoutPeriod->addDateTime($this->getDateTimeHolder()->getCurrent());
        $startedDate = $this->getDateTimeHolder()->getCurrent();

        $inProgressReport = $this->createInProgressReport($startedDate, $timeoutDate);
        $this->storeReport($inProgressReport);

        $report = $this->generateReport();

        $endDate = $this->getDateTimeHolder()->getCurrent();
        $report = $this->completeReport($report, $startedDate, $timeoutDate, $endDate);

        $this->storeReport($report);

        return $report;
    }

    /**
     * @return ReportDtoInterface
     */
    abstract function createEmptyReport();

    private function completeReport(ReportDtoInterface $report, $startedDate, $timeoutDate, $endDate)
    {
        $report->getReportStatus()->setIsCompleted(true);
        $report->getReportStatus()->setGenerationTimeoutDate($timeoutDate);
        $report->getReportStatus()->setGenerationStartTime($startedDate);
        $report->getReportStatus()->setGenerationEndTime($endDate);
        $totalGenerationTime = TimeSpan::subtractDates($endDate, $startedDate);
        $report->getReportStatus()->setGenerationTime($totalGenerationTime);

        return $report;
    }

    public function get()
    {
        $report = $this->getFromStorage();

        $this->checkIfReportHasCorrectStatus($report);

        if ($this->reportIsInProgress($report)) {
            return $this->returnInProgressReportDto();
        } else {
            if ($this->reportNeedsToBeGenerated($report)) {
                $report = $this->generateReportAndStoreIt();
            }

            return $report;
        }
    }

    protected function getDateTimeHolder()
    {
        return $this->dateTimeHolder;
    }

    protected function checkIfReportHasCorrectStatus(ReportDtoInterface $report = null)
    {
        if ($report !== null
            && !$report->getReportStatus()->getIsCompleted()
            && $report->getReportStatus()->getGenerationTimeoutDate() === null
        ) {
            throw new \UnexpectedValueException("Timeout not set for a report that is being generated.");
        }
    }

    /**
     * @return ReportDtoInterface
     */
    abstract protected function returnInProgressReportDto();

    /**
     * @return ReportDtoInterface
     */
    abstract protected function generateReport();

    protected function reportIsInProgress(ReportDtoInterface $report = null)
    {
        return $report !== null
        && !$report->getReportStatus()->getIsCompleted()
        && !$this->reportGenerationHasTimedOut($report);
    }

    protected function reportNeedsToBeGenerated(ReportDtoInterface $report = null)
    {
        return $this->reportHasNotBeenGenerated($report)
        || $this->reportGenerationHasTimedOut($report);
    }

    protected function reportHasNotBeenGenerated(ReportDtoInterface $report = null)
    {
        return $report === null;
    }

    protected function reportGenerationHasTimedOut(ReportDtoInterface $report = null)
    {
        return !$report->getReportStatus()->getIsCompleted()
        && $report->getReportStatus()->getGenerationTimeoutDate() <= $this->dateTimeHolder->getCurrent();
    }
}
