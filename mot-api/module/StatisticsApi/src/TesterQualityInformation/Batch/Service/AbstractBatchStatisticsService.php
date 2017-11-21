<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator\IsDateInFutureValidator;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Date\DateRange;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\Month;
use DvsaFeature\FeatureToggles;

abstract class AbstractBatchStatisticsService
{
    private $dateTimeHolder;
    private $featureToggles;

    public function __construct(
        DateTimeHolderInterface $dateTimeHolder,
        FeatureToggles $featureToggles = null
    ) {
        $this->dateTimeHolder = $dateTimeHolder;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param Month $month
     * @throws \InvalidArgumentException
     */
    protected function validateDate(Month $month)
    {
        $startDate = $month->getStartDate();
        $endDate = $month->getEndDate();

        $validator = new IsDateInFutureValidator($this->dateTimeHolder);

        if ($validator->isValid($startDate) || $validator->isValid($endDate)) {
            throw new \InvalidArgumentException("Cannot generate statistics for date in the future");
        }
    }
    /**
     * @return int[]
     */
    protected function getMonthRanges(): array
    {
        $monthRanges[] = LastMonthsDateRange::ONE_MONTH;
        if ($this->featureToggles->isDisabled(FeatureToggle::GQR_DISABLE_3_MONTHS_ENDPOINTS)) {
            $monthRanges[] = LastMonthsDateRange::THREE_MONTHS;
        }
        return $monthRanges;
    }

    /**
     * @param Month $month
     * @return DateRange[]
     */
    protected function getMonthRangesForDate(Month $month): array
    {
        $oneMonthAgo = new DateRange($month->getStartDate(), $month->getEndDate());
        $statisticsDateRanges[] = $oneMonthAgo;
        if ($this->featureToggles->isDisabled(FeatureToggle::GQR_DISABLE_3_MONTHS_ENDPOINTS)) {
            $threeMonthsAgo = $month->previous()->previous();
            $statisticsDateRanges[] = new DateRange($threeMonthsAgo->getStartDate(), $oneMonthAgo->getEndDate());
        }
        return $statisticsDateRanges;
    }

}