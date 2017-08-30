<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use Site\Form\TQIMonthRangeForm;

class TestQualityInformationViewModel
{
    /** @var int $monthRange */
    private $monthRange;

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

    /** @var TQIMonthRangeForm $monthRangeForm */
    private $monthRangeForm;

    /** @var DateTimeHolderInterface */
    private $dateTimeHolderInterface;


    /**
     * @param TesterPerformanceDto         $testerPerformance
     * @param array                        $groupASiteTests
     * @param array                        $groupBSiteTests
     * @param NationalPerformanceReportDto $nationalPerformanceStatisticsDto
     * @param TesterAuthorisation          $personAuthorisation
     * @param int                          $monthRange
     * @param $returnLink
     * @param $returnLinkText
     * @param TQIMonthRangeForm $monthRangeForm
     * @param DateTimeHolderInterface $dateTimeHolderInterface
     * @param int $targetPersonId
     */
    public function __construct(
        TesterPerformanceDto $testerPerformance,
        array $groupASiteTests,
        array $groupBSiteTests,
        NationalPerformanceReportDto $nationalPerformanceStatisticsDto = null,
        TesterAuthorisation $personAuthorisation,
        int $monthRange,
        $returnLink,
        $returnLinkText,
        TQIMonthRangeForm $monthRangeForm,
        DateTimeHolderInterface $dateTimeHolderInterface,
        int $targetPersonId
    ) {
        $this->monthRange = $monthRange;
        $this->returnLink = $returnLink;
        $this->returnLinkText = $returnLinkText;
        $this->monthRangeForm = $monthRangeForm;
        $this->dateTimeHolderInterface = $dateTimeHolderInterface;

        $this->a = new GroupStatisticsTable(
            $testerPerformance->getGroupAPerformance(),
            $groupASiteTests,
            $nationalPerformanceStatisticsDto->getReportStatus()->getIsCompleted() ?: false,
            $nationalPerformanceStatisticsDto->getGroupA() ?: null,
            'Class 1 and 2',
            VehicleClassGroupCode::BIKES,
            $targetPersonId
        );

        $this->b = new GroupStatisticsTable(
            $testerPerformance->getGroupBPerformance(),
            $groupBSiteTests,
            $nationalPerformanceStatisticsDto->getReportStatus()->getIsCompleted() ?: false,
            $nationalPerformanceStatisticsDto->getGroupB() ?: null,
            'Class 3, 4, 5 and 7', VehicleClassGroupCode::CARS_ETC,
            $targetPersonId
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
    }

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    public function getReturnLinkText()
    {
        return $this->returnLinkText;
    }

    public function getA():GroupStatisticsTable
    {
        return $this->a;
    }

    public function getB():GroupStatisticsTable
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
     * @return TQIMonthRangeForm
     */
    public function getMonthRangeForm():TQIMonthRangeForm
    {
        return $this->monthRangeForm;
    }

    /**
     * @return string
     */
    public function getDateRangeWording():string
    {
        $range = new LastMonthsDateRange($this->dateTimeHolderInterface);
        $range->setNumberOfMonths($this->monthRange);

        return $range->__toString();
    }

    public function getQueryParams():array
    {
        $params = ['monthRange' => $this->monthRange];

        return [
            'query' => $params
        ];
    }
}
