<?php
namespace Site\Action;

use Core\Action\ViewActionResult;
use Core\Action\FileAction;
use Core\Action\NotFoundActionResult;
use Core\Routing\ProfileRoutes;
use Core\Routing\VtsRouteList;
use Core\Routing\VtsRoutes;
use DateTime;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Mapper\UserTestQualityCsvMapper;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Mvc\Router\Http\RouteMatch;

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
    private $contextProvider;
    private $routeMatch;
    private $identityProvider;

    public function __construct(
        ComponentFailRateApiResource $componentFailRateApiResource,
        NationalComponentStatisticApiResource $nationalComponentStatisticApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        ViewVtsTestQualityAssertion $assertion,
        SiteMapper $siteMapper,
        ContextProvider $contextProvider,
        RouteMatch $routeMatch,
        MotIdentityProviderInterface $identityProvider
    )
    {
        $this->assertion = $assertion;
        $this->componentFailRateApiResource = $componentFailRateApiResource;
        $this->nationalComponentStatisticApiResource = $nationalComponentStatisticApiResource;
        $this->siteMapper = $siteMapper;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->contextProvider = $contextProvider;
        $this->routeMatch = $routeMatch;
        $this->identityProvider = $identityProvider;
    }

    public function execute($siteId, $userId, $month, $year, $groupCode, array $breadcrumbs, $isReturnToAETQI, Url $urlPlugin)
    {
        if($this->identityProvider->getIdentity()->getUserId() != $userId) {
            $this->assertion->assertGranted($siteId);
        }

        try {
            $this->viewedDate = $this->setMonthAndYear($month, $year);
        } catch (IncorrectDateFormatException $ie) {
            return new NotFoundActionResult();
        }

        $userBreakdown = $this->componentFailRateApiResource->getForTesterAtSite($siteId, $userId, $groupCode, $month, $year);
        $nationalBreakdown = $this->nationalComponentStatisticApiResource->getForDate($groupCode, $month, $year);
        $nationalGroupPerformance = $this->getNationalGroupPerformance($groupCode, $month, $year);

        if ($this->contextProvider->isYourProfileContext()) {
            $returnLink = ProfileRoutes::of($urlPlugin)->yourProfileTqi($month, $year);
            $showCsvLink = false;
        } elseif ($this->contextProvider->isUserSearchContext()) {
            $returnLink = ProfileRoutes::of($urlPlugin)->userSearchTqi($userId, $month, $year);
            $showCsvLink = false;
        } else {
            $query = [];
            if ($isReturnToAETQI) {
                $query['returnToAETQI'] = true;
            }

            $returnLink = VtsRoutes::of($urlPlugin)->vtsTestQuality($siteId, $month, $year, $query);
            $showCsvLink = true;

            if ($this->routeMatch->getMatchedRouteName() === VtsRouteList::VTS_USER_PROFILE_TEST_QUALITY) {
                $showCsvLink = false;
                $returnLink = $urlPlugin->fromRoute("newProfileVTS/test-quality-information", ["vehicleTestingStationId" => $siteId, "id" => $userId, "month" => $month, "year" => $year]);
            }

            if($this->checkIfUserHasTests($userBreakdown)) {
                $this->site = $this->siteMapper->getById($siteId);
                $csvMapper = new UserTestQualityCsvMapper($userBreakdown, $nationalBreakdown, $nationalGroupPerformance, $this->site, $groupCode, $month, $year);
                $csvSizeInBytes = $csvMapper->toCsvFile()->getSizeInBytes();
            }
        }

        if ($this->checkIfUserHasTests($userBreakdown)) {
            if (
                $this->contextProvider->isYourProfileContext() ||
                $this->contextProvider->isUserSearchContext() ||
                ($this->routeMatch->getMatchedRouteName() === VtsRouteList::VTS_USER_PROFILE_TEST_QUALITY)
            ) {
                $breadcrumbs[$userBreakdown->getSiteName()] = null;
            } else {
                $breadcrumbs += [self::PAGE_TITLE => $returnLink, $userBreakdown->getDisplayName() => null];
            }

            return $this->buildActionResult(
                new UserTestQualityViewModel($userBreakdown,
                    $nationalGroupPerformance,
                    $nationalBreakdown,
                    $groupCode,
                    $userId,
                    $siteId,
                    $this->viewedDate,
                    isset($csvSizeInBytes) ? $csvSizeInBytes : 0,
                    $returnLink,
                    $showCsvLink
                ),
                $breadcrumbs,
                $userBreakdown->getDisplayName(),
                $userBreakdown->getSiteName()
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

    private function buildActionResult(UserTestQualityViewModel $vm, array $breadcrumbs, $userName, $siteName)
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('site/user-test-quality');
        $actionResult->layout()->setPageTitle($userName);
        $actionResult->layout()->setPageSubTitle(static::PAGE_TITLE);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTertiaryTitle($this->getTertiaryTitle($siteName));

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

    private function getTertiaryTitle($siteName)
    {
        return "Tests done at " . $siteName . " in " . $this->viewedDate->format("F Y");
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