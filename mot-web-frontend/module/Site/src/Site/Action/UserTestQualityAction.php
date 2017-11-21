<?php

namespace Site\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Core\BackLink\BackLinkQueryParam;
use Core\Routing\PerformanceDashboardRoutes;
use Core\Routing\ProfileRoutes;
use Core\Routing\VtsRouteList;
use Core\Routing\VtsRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\SiteComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Dto\Security\RolesMapDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Form\TQIMonthRangeForm;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Mvc\Router\Http\RouteMatch;
use DvsaFeature\FeatureToggles;
use DvsaCommon\Constants\FeatureToggle;

class UserTestQualityAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Test quality information';
    const THREE_MONTHS_RANGE = 3;
    const ONE_MONTH_RANGE = 1;

    private $assertion;
    private $componentFailRateApiResource;
    private $nationalComponentStatisticApiResource;
    private $siteComponentFailRateApiResource;
    private $siteMapper;

    /** @var SiteDto */
    private $site;

    private $nationalPerformanceApiResource;
    private $contextProvider;
    private $routeMatch;
    private $identityProvider;
    private $dateTimeHolder;
    private $featureToggles;

    public function __construct(
        ComponentFailRateApiResource $componentFailRateApiResource,
        NationalComponentStatisticApiResource $nationalComponentStatisticApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        SiteComponentFailRateApiResource $siteComponentFailRateApiResource,
        ViewVtsTestQualityAssertion $assertion,
        SiteMapper $siteMapper,
        ContextProvider $contextProvider,
        RouteMatch $routeMatch,
        MotIdentityProviderInterface $identityProvider,
        DateTimeHolder $dateTimeHolder,
        FeatureToggles $featureToggles
    ) {
        $this->assertion = $assertion;
        $this->componentFailRateApiResource = $componentFailRateApiResource;
        $this->nationalComponentStatisticApiResource = $nationalComponentStatisticApiResource;
        $this->siteComponentFailRateApiResource = $siteComponentFailRateApiResource;
        $this->siteMapper = $siteMapper;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->contextProvider = $contextProvider;
        $this->routeMatch = $routeMatch;
        $this->identityProvider = $identityProvider;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->featureToggles = $featureToggles;
    }

    public function execute($siteId, $userId, int $monthRange, $groupCode, array $breadcrumbs, $isReturnToAETQI, Url $urlPlugin)
    {
        $gqr3MonthsViewEnabled = $this->featureToggles->isEnabled(FeatureToggle::GQR_REPORTS_3_MONTHS_OPTION);

        if ($this->identityProvider->getIdentity()->getUserId() != $userId) {
            $this->assertion->assertGranted($siteId);
        }

        $userBreakdown = $this->componentFailRateApiResource->getForTesterAtSite($siteId, $userId, $groupCode, $monthRange);

        $queryParams = ['monthRange' => $monthRange];
        $monthDateRange = new LastMonthsDateRange($this->dateTimeHolder);
        $monthDateRange->setNumberOfMonths($monthRange);
        $monthRangeForm = (new TQIMonthRangeForm($gqr3MonthsViewEnabled))
            ->setValue($monthRange)
            ->setBackTo($isReturnToAETQI);
        if(!$monthRangeForm->isValid($monthRange)){
            return new NotFoundActionResult();
        }

        $nationalBreakdown = $this->nationalComponentStatisticApiResource->getForDate($groupCode, $monthRange);
        $nationalGroupPerformance = $this->getNationalGroupPerformance($groupCode, $monthRange);
        $siteAverageBreakdown = $this->siteComponentFailRateApiResource->get($siteId, $groupCode, $monthRange);

        if ($this->contextProvider->isYourProfileContext()) {
            $returnLink = ProfileRoutes::of($urlPlugin)->yourProfileTqi($queryParams);
        } elseif ($this->contextProvider->isPerformanceDashboardContext()) {
            $returnLink = PerformanceDashboardRoutes::of($urlPlugin)->performanceDashboardTqi($queryParams);
        } elseif ($this->contextProvider->isUserSearchContext()) {
            $returnLink = ProfileRoutes::of($urlPlugin)->userSearchTqi($userId, $queryParams);
        } else {
            if ($isReturnToAETQI) {
                $queryParams[BackLinkQueryParam::RETURN_TO_AE_TQI] = true;
            }

            $returnLink = VtsRoutes::of($urlPlugin)->vtsTestQuality($siteId, $queryParams);

            if ($this->routeMatch->getMatchedRouteName() === VtsRouteList::VTS_USER_PROFILE_TEST_QUALITY) {
                $returnLink = $urlPlugin->fromRoute('newProfileVTS/test-quality-information', ['vehicleTestingStationId' => $siteId, 'id' => $userId], ['query' => $queryParams]);
            }
        }

        if (
            $this->contextProvider->isYourProfileContext() ||
            $this->contextProvider->isPerformanceDashboardContext() ||
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
                $siteAverageBreakdown->getComponents(),
                $groupCode,
                $userId,
                $siteId,
                $monthDateRange,
                $returnLink,
                $monthRangeForm
            ),
            $breadcrumbs,
            $userBreakdown->getDisplayName(),
            $userBreakdown->getSiteName()
        );
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
        $actionResult->layout()->setPageTertiaryTitle($siteName);

        return $actionResult;
    }

    /**
     * @param ComponentBreakdownDto $userBreakdown
     *
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
     * @param string $groupCode
     * @param int $monthRange
     * @return MotTestingPerformanceDto
     */
    private function getNationalGroupPerformance(string $groupCode, int $monthRange):MotTestingPerformanceDto
    {
        $nationalPerformance = $this->nationalPerformanceApiResource->getForMonths($monthRange);

        switch ($groupCode) {
            case VehicleClassGroupCode::BIKES:
                $nationalGroupPerformance = $nationalPerformance->getGroupA();
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $nationalGroupPerformance = $nationalPerformance->getGroupB();
                break;
            default:
                throw new \InvalidArgumentException('Wrong vehicle group code');
        }

        return $nationalGroupPerformance;
    }

}
