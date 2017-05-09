<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation;

use DateTime;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Month;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

class TestQualityInformationViewModel
{
    const POSSIBLE_MONTHS_COUNT = 12;

    /** @var DateTime $date */
    private $date;

    /** @var string $returnLink */
    private $returnLink;

    /** @var string $returnLinkText */
    private $returnLinkText;

    /** @var GroupStatisticsTable $a */
    private $a;

    /** @var GroupStatisticsTable $b */
    private $b;

    /** @var bool */
    private $isAViewable;

    /** @var bool */
    private $isBViewable;

    /** @var TestQualityInformationMonthFilter $monthFilter */
    private $monthFilter;

    /**
     * @param TesterPerformanceDto         $testerPerformance
     * @param array                        $groupASiteTests
     * @param array                        $groupBSiteTests
     * @param NationalPerformanceReportDto $nationalPerformanceStatisticsDto
     * @param TesterAuthorisation          $personAuthorisation
     * @param DateTime                     $date
     * @param $returnLink
     * @param $returnLinkText
     * @param $componentBreakdownLinkText
     * @param $componentBreakdownLinkTextGroup
     * @param TestQualityInformationMonthFilter $monthFilter
     */
    public function __construct(
        TesterPerformanceDto $testerPerformance,
        array $groupASiteTests,
        array $groupBSiteTests,
        NationalPerformanceReportDto $nationalPerformanceStatisticsDto = null,
        TesterAuthorisation $personAuthorisation,
        $date,
        $returnLink,
        $returnLinkText,
        $componentBreakdownLinkText,
        $componentBreakdownLinkTextGroup,
        $monthFilter
    ) {
        $this->date = $date;
        $this->returnLink = $returnLink;
        $this->returnLinkText = $returnLinkText;

        $this->a = new GroupStatisticsTable(
            $testerPerformance->getGroupAPerformance(),
            $groupASiteTests,
            $nationalPerformanceStatisticsDto->getReportStatus()->getIsCompleted() ?: false,
            $nationalPerformanceStatisticsDto->getGroupA() ?: null,
            'Class 1 and 2',
            VehicleClassGroupCode::BIKES,
            $componentBreakdownLinkText,
            $componentBreakdownLinkTextGroup,
            $this->getComponentBreakdownLink(VehicleClassGroupCode::BIKES)
        );

        $this->b = new GroupStatisticsTable(
            $testerPerformance->getGroupBPerformance(),
            $groupBSiteTests,
            $nationalPerformanceStatisticsDto->getReportStatus()->getIsCompleted() ?: false,
            $nationalPerformanceStatisticsDto->getGroupB() ?: null,
            'Class 3, 4, 5 and 7', VehicleClassGroupCode::CARS_ETC,
            $componentBreakdownLinkText,
            $componentBreakdownLinkTextGroup,
            $this->getComponentBreakdownLink(VehicleClassGroupCode::CARS_ETC)
        );

        $this->isAViewable = $personAuthorisation->getGroupAStatus()->getCode() === AuthorisationForTestingMotStatusCode::QUALIFIED;
        if (!empty($testerPerformance->getGroupAPerformance())) {
            $this->isAViewable = $this->isAViewable || $testerPerformance->getGroupAPerformance()->getTotal() > 0;
        }

        $this->isBViewable = $personAuthorisation->getGroupBStatus()->getCode() === AuthorisationForTestingMotStatusCode::QUALIFIED;
        if (!empty($testerPerformance->getGroupBPerformance())) {
            $this->isBViewable = $this->isBViewable || $testerPerformance->getGroupBPerformance()->getTotal() > 0;
        }

        if (!($this->isAViewable || $this->isBViewable)) {
            $this->isAViewable = true;
            $this->isBViewable = true;
        }

        $oneMonthAgo = DateUtils::subtractCalendarMonths(DateUtils::toUserTz(new \DateTime()), 1);
        $startMonth = new Month($oneMonthAgo->format('Y'), $oneMonthAgo->format('m'));
        $viewedMonth = new Month($this->date->format('Y'), $this->date->format('m'));

        $this->monthFilter = $monthFilter
            ->setNumberOfMonthsBack(self::POSSIBLE_MONTHS_COUNT)
            ->setStartMonth($startMonth)
            ->setViewedMonth($viewedMonth);
    }

    public function getMonthFilter()
    {
        return $this->monthFilter;
    }

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    public function getReturnLinkText()
    {
        return $this->returnLinkText;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function isAVisible()
    {
        return $this->isAViewable;
    }

    public function isBVisible()
    {
        return $this->isBViewable;
    }

    /**
     * @param string $group
     *
     * @return string
     */
    private function getComponentBreakdownLink($group)
    {
        return $this->returnLink.sprintf('/test-quality-information/%s/components/%s',
            $this->getMonthYear(), $group);
    }

    private function getMonthYear()
    {
        return $this->date->format('m/Y');
    }
}
