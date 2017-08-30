<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service;

use DateTime;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\Month;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Repository\TqiRfrCountRepository;
use DvsaEntities\Repository\TqiTestCountRepository;
use Zend\Validator\Between;

class BatchPersonTestQualityInformationService implements AutoWireableInterface
{
    private $dateTimeHolder;
    private $tqiTestCountRepository;
    private $tqiRfrCountRepository;
    private $monthValidator;

    public function __construct(
        DateTimeHolderInterface $dateTimeHolder,
        TqiTestCountRepository $tqiTestCountRepository,
        TqiRfrCountRepository $tqiRfrCountRepository
    )
    {
        $this->tqiTestCountRepository = $tqiTestCountRepository;
        $this->tqiRfrCountRepository = $tqiRfrCountRepository;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->monthValidator = new Between([
            'min' => LastMonthsDateRange::ONE_MONTH, 'max' => LastMonthsDateRange::THREE_MONTHS
        ]);
    }

    /**
     * @param int $monthsAgo
     */
    public function generatePersonTestStatistics(int $monthsAgo)
    {
        $month = $this->getMonth($monthsAgo);
        $this->deleteOldTestReports();

        if($this->rfrCountsAreNotGenerated($month)) {
            $result = $this->tqiTestCountRepository->populateTableWithData($month->getStartDate(), $month->getEndDate());
            $this->validateQueryResult($result);
        }
    }

    /**
     * @param int $monthsAgo
     */
    public function generatePersonComponentStatistics(int $monthsAgo)
    {
        $month = $this->getMonth($monthsAgo);
        $this->deleteOldRfrReports();

        if($this->testCountsAreNotGenerated($month)) {
            $result = $this->tqiRfrCountRepository->populateTableWithData($month->getStartDate(), $month->getEndDate());
            $this->validateQueryResult($result);
        }
    }

    private function deleteOldTestReports()
    {
        $this->tqiTestCountRepository->deleteStatsOlderThan($this->getEarliestPeriodStartDate());
    }

    private function deleteOldRfrReports()
    {
        $this->tqiRfrCountRepository->deleteStatsOlderThan($this->getEarliestPeriodStartDate());
    }

    /**
     * @param $month
     * @return bool
     */
    private function testCountsAreNotGenerated(Month $month):bool
    {
        return 0 == $this->tqiRfrCountRepository->checkIfThereAreDataForPeriod(
            $month->getStartDate(),
            $month->getEndDate()
        );
    }

    /**
     * @param $month
     * @return bool
     */
    private function rfrCountsAreNotGenerated(Month $month):bool
    {
        return 0 == $this->tqiTestCountRepository->checkIfThereAreDataForPeriod(
            $month->getStartDate(),
            $month->getEndDate()
        );
    }

    private function getMonth(int $monthsAgo): Month
    {
        if(!$this->monthValidator->isValid($monthsAgo)){
            throw new \InvalidArgumentException("You have to provide a valid numer of months ago to generate stats.");
        }

        $month = new Month(
            $this->dateTimeHolder->getCurrent()->format('Y'),
            $this->dateTimeHolder->getCurrent()->format('n')
        );

        for ($i = 0; $i < $monthsAgo; $i++) {
            $month = $month->previous();
        }

        return $month;
    }

    /**
     * @return DateTime
     */
    private function getEarliestPeriodStartDate():DateTime
    {
        return $this->getMonth(LastMonthsDateRange::THREE_MONTHS)->getStartDate();
    }

    private function validateQueryResult($result)
    {
        if(!$result){
            throw new \Exception("Something went wrong while generating TQI DB cache");
        }
    }
}