<?php

namespace Site\Action;

use Core\Action\ViewActionResult;
use Core\Action\FileAction;
use Core\Action\NotFoundActionResult;
use DateTime;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Mapper\SiteTestQualityCsvMapper;
use Site\ViewModel\TestQuality\SiteTestQualityViewModel;
use Site\ViewModel\TestQuality\TestQualityMonthFilter;
use Zend\Mvc\Controller\Plugin\Url;

class SiteTestQualityAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Test quality information';

    private $sitePerformanceApiResource;
    private $nationalPerformanceApiResource;
    private $assertion;
    private $siteMapper;

    /** @var VehicleTestingStationDto */
    private $site;

    /** @var DateTime */
    private $viewedDate;

    public function __construct(
        SitePerformanceApiResource $sitePerformanceApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        SiteMapper $siteMapper,
        ViewVtsTestQualityAssertion $assertion
    ) {
        $this->sitePerformanceApiResource = $sitePerformanceApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->siteMapper = $siteMapper;
        $this->assertion = $assertion;
    }

    public function execute($siteId, $month, $year, $isReturnToAETQI, array $breadcrumbs, Url $url, $params, $queryParams)
    {
        $this->assertion->assertGranted($siteId);

        $breadcrumbs += [self::PAGE_TITLE => null];

        if ($month !== null or $year !== null) {
            try {
                $this->viewedDate = DateUtils::toDateFromParts(1, $month, $year);
            } catch (IncorrectDateFormatException $ie) {
                return new NotFoundActionResult();
            }
        } else {
            $this->viewedDate = $this->setMonthAndYear();
            $month = $this->viewedDate->format('m');
            $year = $this->viewedDate->format('Y');
        }

        $sitePerformance = $this->sitePerformanceApiResource->getForDate($siteId, $month, $year);
        $nationalPerformance = $this->nationalPerformanceApiResource->getForDate($month, $year);
        $this->site = $this->siteMapper->getById($siteId);

        $csvFileSizeGroupA = $this->getCsvMapperForGroupA($sitePerformance, $nationalPerformance, $month, $year)
            ->toCsvFile()->getSizeInBytes();
        $csvFileSizeGroupB = $this->getCsvMapperForGroupB($sitePerformance, $nationalPerformance, $month, $year)
            ->toCsvFile()->getSizeInBytes();

        return $this->buildActionResult(
            new SiteTestQualityViewModel(
                $sitePerformance,
                $nationalPerformance,
                $this->site,
                $this->viewedDate,
                $csvFileSizeGroupA,
                $csvFileSizeGroupB,
                $isReturnToAETQI,
                $this->getMonthFilter($url, $params, $queryParams)
            ),
            $breadcrumbs
        );
    }

    public function getCsv($siteId, $month, $year, $groupCode)
    {
        $this->assertion->assertGranted($siteId);
        $this->site = $this->siteMapper->getById($siteId);
        $nationalPerformance = $this->nationalPerformanceApiResource->getForDate($month, $year);
        $sitePerformance = $this->sitePerformanceApiResource->getForDate($siteId, $month, $year);

        switch ($groupCode) {
            case VehicleClassGroupCode::BIKES:
                $csvMapper = $this->getCsvMapperForGroupA($sitePerformance, $nationalPerformance, $month, $year);
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $csvMapper = $this->getCsvMapperForGroupB($sitePerformance, $nationalPerformance, $month, $year);
                break;
            default:
                throw new \InvalidArgumentException('Wrong group code');
        }

        return new FileAction($csvMapper->toCsvFile());
    }

    private function buildActionResult(SiteTestQualityViewModel $vm, array $breadcrumbs)
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('site/test-quality');

        $actionResult->layout()->setPageSubTitle(self::PAGE_TITLE);
        $actionResult->layout()->setPageTitle($this->getPageTitle());

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');

        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTertiaryTitle($this->getPageTertiaryTitle());

        return $actionResult;
    }

    /**
     * @return DateTime
     */
    private function setMonthAndYear()
    {
        return DateUtils::subtractCalendarMonths(DateUtils::toUserTz(DateUtils::firstOfThisMonth()), 1);
    }

    private function getPageTertiaryTitle()
    {
        return 'Tests done in '.$this->viewedDate->format('F Y');
    }

    private function getPageTitle()
    {
        return $this->site->getName();
    }

    private function getCsvMapperForGroupA(
        SitePerformanceDto $sitePerformance,
        NationalPerformanceReportDto $nationalPerformance,
        $month,
        $year
    ) {
        $csvMapper = new SiteTestQualityCsvMapper(
            $sitePerformance->getA(),
            $nationalPerformance->getReportStatus()->getIsCompleted(),
            $nationalPerformance->getGroupA(),
            $this->site,
            VehicleClassGroupCode::BIKES,
            $month,
            $year
        );

        return $csvMapper;
    }

    private function getCsvMapperForGroupB(
        SitePerformanceDto $sitePerformance,
        NationalPerformanceReportDto $nationalPerformance,
        $month,
        $year
    ) {
        $csvMapper = new SiteTestQualityCsvMapper(
            $sitePerformance->getB(),
            $nationalPerformance->getReportStatus()->getIsCompleted(),
            $nationalPerformance->getGroupB(),
            $this->site,
            VehicleClassGroupCode::CARS_ETC,
            $month,
            $year
        );

        return $csvMapper;
    }

    private function getMonthFilter(Url $url, $params, $queryParams)
    {
        return new TestQualityMonthFilter($params, $queryParams, $url);
    }
}
