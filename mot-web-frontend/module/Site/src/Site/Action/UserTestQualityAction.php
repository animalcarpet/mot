<?php
namespace Site\Action;

use Core\Action\ActionResult;
use Core\Action\FileAction;
use Core\Action\NotFoundActionResult;
use Core\Routing\VtsRoutes;
use DateTime;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaClient\Mapper\SiteMapper;
use Site\Mapper\UserTestQualityCsvMapper;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;

class UserTestQualityAction implements AutoWireableInterface
{
    const PAGE_TITLE = "Test quality information";

    private $assertion;
    private $componentFailRateApiResource;
    private $nationalComponentStatisticApiResource;
    private $siteMapper;

    /** @var SiteDto */
    private $site;

    /** @var DateTime */
    private $viewedDate;
    private $nationalPerformanceApiResource;

    public function __construct(
        ComponentFailRateApiResource $componentFailRateApiResource,
        NationalComponentStatisticApiResource $nationalComponentStatisticApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        ViewVtsTestQualityAssertion $assertion,
        SiteMapper $siteMapper
    ) {
        $this->assertion = $assertion;
        $this->componentFailRateApiResource = $componentFailRateApiResource;
        $this->nationalComponentStatisticApiResource = $nationalComponentStatisticApiResource;
        $this->siteMapper = $siteMapper;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
    }

    public function execute($siteId, $userId, $month, $year, $groupCode, array $breadcrumbs, $isReturnToAETQI, $urlPlugin)
    {
        $this->assertion->assertGranted($siteId);

        try {
            $this->viewedDate = $this->setMonthAndYear($month, $year);
        } catch (IncorrectDateFormatException $ie) {
            return new NotFoundActionResult();
        }

        $userBreakdown = $this->componentFailRateApiResource->getForTesterAtSite($siteId, $userId, $groupCode, $month, $year);
        $nationalBreakdown = $this->nationalComponentStatisticApiResource->getForDate($groupCode, $month, $year);
        $nationalGroupPerformance = $this->getNationalGroupPerformance($groupCode, $month, $year);
        $this->site = $this->siteMapper->getById($siteId);

        $returnLink = VtsRoutes::of($urlPlugin)->vtsTestQuality($siteId, $month, $year);

        $csvMapper = new UserTestQualityCsvMapper($userBreakdown, $nationalBreakdown, $nationalGroupPerformance, $this->site, $groupCode, $month, $year);

        if ($this->checkIfUserHasTests($userBreakdown)) {
            $breadcrumbs += [self::PAGE_TITLE => $returnLink, $userBreakdown->getDisplayName() => null];

            return $this->buildActionResult(
                new UserTestQualityViewModel($userBreakdown,
                    $nationalGroupPerformance,
                    $nationalBreakdown,
                    $groupCode,
                    $userId,
                    $this->site,
                    $this->viewedDate,
                    $csvMapper->toCsvFile()->getSizeInBytes(),
                    $isReturnToAETQI
                ),
                $breadcrumbs,
                $userBreakdown->getDisplayName()
            );
        } else {
            return new NotFoundActionResult();
        }

    }

    public function getCsv($siteId, $userId, $month, $year, $groupCode)
    {
        $this->assertion->assertGranted($siteId);

        $userBreakdown = $this->componentFailRateApiResource->getForTesterAtSite($siteId, $userId, $groupCode, $month, $year);
        $nationalBreakdown = $this->nationalComponentStatisticApiResource->getForDate($groupCode, $month, $year);
        $nationalGroupPerformance = $this->getNationalGroupPerformance($groupCode, $month, $year);
        $site = $this->siteMapper->getById($siteId);

        $mapper = new UserTestQualityCsvMapper($userBreakdown, $nationalBreakdown, $nationalGroupPerformance, $site,
            $groupCode, $month, $year);

        return new FileAction($mapper->toCsvFile());
    }

    private function buildActionResult(UserTestQualityViewModel $vm, array $breadcrumbs, $userName)
    {
        $actionResult = new ActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('site/user-test-quality');
        $actionResult->layout()->setPageTitle($userName);
        $actionResult->layout()->setPageSubTitle(static::PAGE_TITLE);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTertiaryTitle($this->getTertiaryTitle());

        return $actionResult;
    }

    /**
     * @param ComponentBreakdownDto $userBreakdown
     * @return bool
     */
    private function checkIfUserHasTests(ComponentBreakdownDto $userBreakdown)
    {
        if (!is_null($userBreakdown)
            && !is_null($userBreakdown->getGroupPerformance())
            && (!empty($userBreakdown->getGroupPerformance()->getTotal()))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $month
     * @param $year
     * @return DateTime
     * @throws IncorrectDateFormatException
     */
    private function setMonthAndYear($month, $year)
    {
        return DateUtils::toDateFromParts(1, $month, $year);
    }

    private function getTertiaryTitle()
    {
        return "Tests done at " . $this->getGarageName() . " in " . $this->viewedDate->format("F Y");
    }

    private function getGarageName()
    {
        return $this->site->getName();
    }

    private function getNationalGroupPerformance($groupCode, $month, $year)
    {
        $nationalPerformance = $this->nationalPerformanceApiResource->getForDate($month, $year);

        switch($groupCode) {
            case VehicleClassGroupCode::BIKES;
                $nationalGroupPerformance = $nationalPerformance->getGroupA();
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $nationalGroupPerformance = $nationalPerformance->getGroupB();
                break;
            default:
                throw new \InvalidArgumentException("Wrong vehicle group code");
        }

        return $nationalGroupPerformance;
    }
}