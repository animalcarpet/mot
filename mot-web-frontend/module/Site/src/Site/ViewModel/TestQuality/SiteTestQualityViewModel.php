<?php
namespace Site\ViewModel\TestQuality;

use Core\Routing\AeRouteList;
use Core\Routing\VtsRouteList;
use DateTime;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use Organisation\Presenter\UrlPresenterData;

class SiteTestQualityViewModel
{
    const POSSIBLE_MONTHS_COUNT = 12;
    const RETURN_TO_VTS = 'Return to vehicle testing station';
    const RETURN_TO_AE_TQI = 'Return to vehicle testing stations';

    /** @var GroupStatisticsTable */
    private $a;
    /** @var GroupStatisticsTable */
    private $b;

    private $isAViewable;
    private $isBViewable;

    /** @var VehicleTestingStationDto */
    private $site;

    /** @var DateTime */
    private $viewedDate;

    /** @var  UrlPresenterData */
    private $returnLink;

    /** @var  bool */
    private $isReturnLinkToAETQI;

    public function __construct(
        SitePerformanceDto $sitePerformanceDto,
        NationalPerformanceReportDto $nationalPerformanceStatisticsDto,
        $site,
        DateTime $viewedDate,
        $csvFileSizeGroupA,
        $csvFileSizeGroupB,
        $isReturnLinkToAETQI
    ) {
        $this->a = new GroupStatisticsTable(
            $sitePerformanceDto->getA(),
            $nationalPerformanceStatisticsDto->getReportStatus()->getIsCompleted(),
            $nationalPerformanceStatisticsDto->getGroupA(),
            'A',
            'Class 1 and 2',
            VehicleClassGroupCode::BIKES,
            $site,
            $viewedDate,
            $csvFileSizeGroupA
        );

        $this->b = new GroupStatisticsTable(
            $sitePerformanceDto->getB(),
            $nationalPerformanceStatisticsDto->getReportStatus()->getIsCompleted(),
            $nationalPerformanceStatisticsDto->getGroupB(),
            'B',
            'Class 3, 4, 5 and 7',
            VehicleClassGroupCode::CARS_ETC,
            $site,
            $viewedDate,
            $csvFileSizeGroupB
        );

        $this->site = $site;
        $siteClasses = $this->site->getTestClasses();

        $this->isAViewable = ArrayUtils::anyMatch($siteClasses, function ($siteClass) {
            return VehicleClassGroup::isGroup($siteClass, VehicleClassGroupCode::BIKES);
        });
        $this->isAViewable = $this->isAViewable || $sitePerformanceDto->getA()->getTotal()->getTotal() > 0;

        $this->isBViewable = ArrayUtils::anyMatch($siteClasses, function ($siteClass) {
            return VehicleClassGroup::isGroup($siteClass, VehicleClassGroupCode::CARS_ETC);
        });
        $this->isBViewable = $this->isBViewable || $sitePerformanceDto->getB()->getTotal()->getTotal() > 0;

        if (!($this->isAViewable || $this->isBViewable)) {
            $this->isAViewable = true;
            $this->isBViewable = true;
        }

        $this->viewedDate = $viewedDate;

        if ($isReturnLinkToAETQI)
        {
            $this->returnLink = new UrlPresenterData(self::RETURN_TO_AE_TQI, AERouteList::AE_TEST_QUALITY, ['id' => $this->site->getOrganisation()->getId()]);
        } else {
            $this->returnLink = new UrlPresenterData(self::RETURN_TO_VTS, VtsRouteList::VTS, ['id' => $this->getSiteId()]);
        }

        $this->isReturnLinkToAETQI = $isReturnLinkToAETQI;
    }

    public function canGroupSectionBeViewed($group)
    {
        TypeCheck::assertEnum($group, VehicleClassGroupCode::class);

        return $group === VehicleClassGroupCode::BIKES ? $this->isAViewable : $this->isBViewable;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function getTestingPeriodTitle()
    {
        return "Initial tests " . date("F o", strtotime('last month'));
    }

    public function getSiteId()
    {
        return $this->site->getId();
    }

    public function getMonthsNames()
    {
        $now = DateUtils::toUserTz(new \DateTime());
        $list = [];

        for ($i = 1; $i <= self::POSSIBLE_MONTHS_COUNT; $i++) {
            $date = DateUtils::subtractCalendarMonths($now, $i);
            $list[$date->format("Y")][$date->format("m")] = [$date->format("F")];
        }

        return $list;
    }

    public function getViewedMonthName()
    {
        return $this->viewedDate->format("F");
    }

    public function getParamsForLinkToMonth($month, $year)
    {
        $params = [
            'id' => $this->getSiteId(),
            'month' => $month,
            'year' => $year,
        ];

        return $params;
    }

    public function getQueryParams()
    {
        if ($this->isReturnLinkToAETQI) {
            return [
                'query' => [
                    'returnToAETQI' => true,
                ]
            ];
        } else {
            return [];
        }
    }

    /** return UrlPresenterData */
    public function getReturnLink()
    {
        return $this->returnLink;
    }
}