<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action;

use Application\Data\ApiPersonalDetails;
use Core\Action\AbstractActionResult;
use Core\Action\NotFoundActionResult;
use Core\Action\ViewActionResult;
use Core\Formatting\AddressFormatter;
use Core\Routing\PerformanceDashboardRoutes;
use Core\Routing\ProfileRoutes;
use Core\Routing\VtsRoutes;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\SiteRowViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiBreadcrumbs;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterMultiSitePerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Formatting\PersonFullNameFormatter;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use DvsaFeature\FeatureToggles;
use DvsaCommon\Constants\FeatureToggle;
use Site\Form\TQIMonthRangeForm;
use Zend\Mvc\Controller\Plugin\Url;

class TestQualityAction implements AutoWireableInterface
{
    const SUBTITLE_YOUR_PROFILE = 'Your profile';
    const SUBTITLE_USER_PROFILE = 'User profile';
    const SUBTITLE_PERFORMANCE_DASHBOARD = 'Performance dashboard';
    const PAGE_LEDE = 'This information will help you manage the
        quality of your tests. How you use it will depend on how many
        vehicles you test and the vehicle type.';
    const returnLinkMap = [
        ContextProvider::YOUR_PROFILE_CONTEXT => self::SUBTITLE_YOUR_PROFILE,
        ContextProvider::PERFORMANCE_DASHBOARD_CONTEXT => self::SUBTITLE_PERFORMANCE_DASHBOARD,
        'default' => self::SUBTITLE_USER_PROFILE
    ];

    /** @var TesterPerformanceApiResource $testerPerformanceApiResource */
    private $testerPerformanceApiResource;

    /** @var NationalPerformanceApiResource $nationalPerformanceApiResource */
    private $nationalPerformanceApiResource;

    /** @var string $returnLinkSuffix */
    private $returnLinkSuffix;

    /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;

    /** @var ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion */
    private $viewTesterTestQualityAssertion;

    /** @var PersonProfileRoutes $personProfileRoutes */
    private $personProfileRoutes;

    private $multiSiteApiResource;

    private $testerTqiBreadcrumbs;

    private $contextProvider;

    private $url;

    private $dateTimeHolder;

    private $personalDetailsService;

    public function __construct(
        TesterPerformanceApiResource $testerPerformanceApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        ContextProvider $contextProvider,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion,
        PersonProfileRoutes $personProfileRoutes,
        TesterMultiSitePerformanceApiResource $multiSiteApiResource,
        TesterTqiBreadcrumbs $testerTqiBreadcrumbs,
        DateTimeHolder $dateTimeHolder,
        ApiPersonalDetails $personalDetailsService,
        FeatureToggles $featureToggles
    ) {
        $this->testerPerformanceApiResource = $testerPerformanceApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->viewTesterTestQualityAssertion = $viewTesterTestQualityAssertion;
        $this->personProfileRoutes = $personProfileRoutes;
        $this->multiSiteApiResource = $multiSiteApiResource;
        $this->testerTqiBreadcrumbs = $testerTqiBreadcrumbs;
        $this->contextProvider = $contextProvider;
        $this->buildReturnLinkSuffix($contextProvider->getContext());
        $this->dateTimeHolder = $dateTimeHolder;
        $this->personalDetailsService = $personalDetailsService;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param int $targetPersonId
     * @param int $monthRange
     * @param Url $url
     * @param $requestParams
     *
     * @return AbstractActionResult
     */
    public function execute(
        int $targetPersonId,
        int $monthRange,
        Url $url,
        $requestParams
    ) {
        $personAuthorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($targetPersonId);
        $this->viewTesterTestQualityAssertion->assertGranted($targetPersonId, $personAuthorisation);

        $this->url = $url;

        $gqr3MonthsViewEnabled = $this->featureToggles->isEnabled(FeatureToggle::GQR_REPORTS_3_MONTHS_OPTION);

        $monthRangeForm = (new TQIMonthRangeForm($gqr3MonthsViewEnabled))
            ->setValue($monthRange);
        if(!$monthRangeForm->isValid($monthRange)){
            return new NotFoundActionResult();
        }

        /** @var TesterPerformanceDto $testerPerformance */
        $testerPerformance = $this->testerPerformanceApiResource->get($targetPersonId, $monthRange);
        /** @var NationalPerformanceReportDto $nationalPerformance */
        $nationalPerformance = $this->nationalPerformanceApiResource->getForMonths($monthRange);

        $siteStats = $this->multiSiteApiResource->get($targetPersonId, $monthRange);

        $groupAStats = $this->mapSiteStatsDtoToViewModel($siteStats->getA(), $monthRange, $targetPersonId, VehicleClassGroupCode::BIKES);
        $groupBStats = $this->mapSiteStatsDtoToViewModel($siteStats->getB(), $monthRange, $targetPersonId, VehicleClassGroupCode::CARS_ETC);

        $returnLink = $this->url->fromRoute($this->personProfileRoutes->getRoute(), $requestParams);

        return $this->buildActionResult(
            new TestQualityInformationViewModel(
                $testerPerformance,
                $groupAStats,
                $groupBStats,
                $nationalPerformance,
                $personAuthorisation,
                $monthRange,
                $returnLink,
                $this->getReturnLinkText(),
                $monthRangeForm,
                $this->dateTimeHolder,
                $targetPersonId
            ),
            $this->testerTqiBreadcrumbs->getBreadcrumbs($targetPersonId),
            $this->getTesterName($targetPersonId)
        );
    }

    /**
     * @param array $siteTestsDtoArray
     * @param int $monthRange
     * @param int $targetPersonId
     * @param string $group
     * @return SiteRowViewModel[]
     */
    private function mapSiteStatsDtoToViewModel(array $siteTestsDtoArray, int $monthRange, int $targetPersonId, string $group)
    {
        TypeCheck::isCollectionOfClass($siteTestsDtoArray, TesterMultiSitePerformanceDto::class);

        $comparator = function (TesterMultiSitePerformanceDto $a, TesterMultiSitePerformanceDto $b) {
            return $b->getTotal() - $a->getTotal();
        };

        usort($siteTestsDtoArray, $comparator);

        return ArrayUtils::map($siteTestsDtoArray, function (TesterMultiSitePerformanceDto $siteStatsDto) use ($monthRange, $targetPersonId, $group) {
            if ($this->contextProvider->isYourProfileContext()) {
                $tqiComponentsAtSiteUrl = ProfileRoutes::of($this->url)->yourProfileTqiComponentsAtSite($siteStatsDto->getSiteId(), $monthRange, $group);
            } elseif ($this->contextProvider->isPerformanceDashboardContext()) {
                $tqiComponentsAtSiteUrl = PerformanceDashboardRoutes::of($this->url)->performanceDashboardTqiComponentBreakdown(
                    $siteStatsDto->getSiteId(),
                    $group,
                    ['monthRange' => $monthRange]
                );
            } elseif ($this->contextProvider->isUserSearchContext()) {
                $tqiComponentsAtSiteUrl = ProfileRoutes::of($this->url)->userSearchTqiComponentsAtSite($targetPersonId, $siteStatsDto->getSiteId(), $monthRange, $group);
            } else {
                $tqiComponentsAtSiteUrl = VtsRoutes::of($this->url)->vtsUserProfileTestQuality($siteStatsDto->getSiteId(), $targetPersonId, $monthRange, $group);
            }

            $addressLine = $siteStatsDto->getSiteAddress()
                ? (new AddressFormatter())
                ->setAddressPartsGlue(', ')
                ->escapedDtoToMultiLine($siteStatsDto->getSiteAddress())
                : '';

            return new SiteRowViewModel(
                $siteStatsDto->getSiteId(),
                $siteStatsDto->getSiteName(),
                $addressLine,
                $siteStatsDto->getTotal(),
                $siteStatsDto->getIsAverageVehicleAgeAvailable(),
                $siteStatsDto->getAverageVehicleAgeInMonths(),
                $siteStatsDto->getAverageTime(),
                $siteStatsDto->getPercentageFailed(),
                $tqiComponentsAtSiteUrl
            );
        });
    }

    /**
     * @param $vm
     * @param array  $breadcrumbs
     * @param string $testerName
     *
     * @return ViewActionResult
     */
    private function buildActionResult($vm, $breadcrumbs, $testerName)
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('test-quality-information/view');

        $actionResult->layout()->setPageSubTitle('Test quality information');
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTitle($testerName);

        return $actionResult;
    }

    private function buildReturnLinkSuffix(string $context)
    {
        $this->returnLinkSuffix = self::returnLinkMap[$context] ?? self::returnLinkMap['default'];
    }

    private function getProfileDescription()
    {
        return $this->returnLinkSuffix;
    }

    private function getReturnLinkText()
    {
        return 'Return to '.strtolower($this->getProfileDescription());
    }

    /**
     * @param int $testerId
     * @return string
     */
    private function getTesterName(int $testerId):string
    {
        $personalDetails = new PersonalDetails($this
            ->personalDetailsService
            ->getPersonalDetailsData($testerId));

        return (new PersonFullNameFormatter())
            ->format(
                $personalDetails->getFirstName(),
                $personalDetails->getMiddleName(),
                $personalDetails->getSurname()
            );
    }
}
