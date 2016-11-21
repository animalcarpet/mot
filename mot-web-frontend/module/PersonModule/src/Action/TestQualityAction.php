<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action;

use Core\Action\AbstractActionResult;
use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Core\Formatting\AddressFormatter;
use Core\Routing\ProfileRoutes;
use Core\Routing\VtsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\SiteRowViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationMonthFilter;
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
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use Zend\Mvc\Controller\Plugin\Url;

class TestQualityAction implements AutoWireableInterface
{
    const SUBTITLE_YOUR_PROFILE = 'Your profile';
    const SUBTITLE_USER_PROFILE = 'User profile';
    const COMPONENT_BREAKDOWN_LINK_TEXT = 'failures by category in %s %s';
    const COMPONENT_BREAKDOWN_LINK_TEXT_GROUP = 'View %sGroup %s ';

    /** @var  TesterPerformanceApiResource $testerPerformanceApiResource */
    private $testerPerformanceApiResource;

    /** @var  NationalPerformanceApiResource $nationalPerformanceApiResource */
    private $nationalPerformanceApiResource;

    /** @var  bool $isYourProfile */
    private $isYourProfile;

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

    public function __construct(
        TesterPerformanceApiResource $testerPerformanceApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        ContextProvider $contextProvider,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion,
        PersonProfileRoutes $personProfileRoutes,
        TesterMultiSitePerformanceApiResource $multiSiteApiResource,
        TesterTqiBreadcrumbs $testerTqiBreadcrumbs
    )
    {
        $this->testerPerformanceApiResource = $testerPerformanceApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->viewTesterTestQualityAssertion = $viewTesterTestQualityAssertion;
        $this->personProfileRoutes = $personProfileRoutes;
        $this->multiSiteApiResource = $multiSiteApiResource;
        $this->testerTqiBreadcrumbs = $testerTqiBreadcrumbs;
        $this->contextProvider = $contextProvider;
        $this->setYourProfileFlag($contextProvider->getContext());
    }

    /**
     * @param int $targetPersonId
     * @param int $month
     * @param int $year
     * @param $requestParams
     * @return AbstractActionResult
     */
    public function execute(
        $targetPersonId,
        $month,
        $year,
        Url $url,
        $requestParams
    )
    {
        $this->url = $url;

        $personAuthorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($targetPersonId);
        $this->viewTesterTestQualityAssertion->assertGranted($targetPersonId, $personAuthorisation);

        /** @var TesterPerformanceDto $testerPerformance */
        $testerPerformance = $this->testerPerformanceApiResource->get($targetPersonId, $month, $year);
        /** @var NationalPerformanceReportDto $nationalPerformance */
        $nationalPerformance = $this->nationalPerformanceApiResource->getForDate($month, $year);
        try {
            $date = DateUtils::toDateFromParts(1, $month, $year);
        } catch (IncorrectDateFormatException $ie) {
            return new NotFoundActionResult();
        }

        $siteStats = $this->multiSiteApiResource->get($targetPersonId,  $month, $year);

        $groupAStats = $this->mapSiteStatsDtoToViewModel($siteStats->getA(), $month, $year, $targetPersonId, VehicleClassGroupCode::BIKES);
        $groupBStats = $this->mapSiteStatsDtoToViewModel($siteStats->getB(), $month, $year, $targetPersonId, VehicleClassGroupCode::CARS_ETC);

        $returnLink = $this->url->fromRoute($this->personProfileRoutes->getRoute(), $requestParams);

        $componentBreakdownLinkText = sprintf(
            self::COMPONENT_BREAKDOWN_LINK_TEXT,
            $this->getMonthFullName($date),
            $year
        );

        $componentBreakdownLinkTextGroup = sprintf(
            self::COMPONENT_BREAKDOWN_LINK_TEXT_GROUP,
            $this->isYourProfile ? 'your ' : '',
            '%s'
        );

        return $this->buildActionResult(
            new TestQualityInformationViewModel(
                $testerPerformance,
                $groupAStats,
                $groupBStats,
                $nationalPerformance,
                $personAuthorisation,
                $date,
                $returnLink,
                $this->getReturnLinkText(),
                $componentBreakdownLinkText,
                $componentBreakdownLinkTextGroup,
                $this->getMonthFilter($requestParams)
            ),
            $this->getMonthFullName($date),
            $year,
            $this->testerTqiBreadcrumbs->getBreadcrumbs($targetPersonId)
        );
    }

    /**
     * @param TesterMultiSitePerformanceDto[] $siteTestsDtoArray
     * @return SiteRowViewModel[]
     */
    private function mapSiteStatsDtoToViewModel(array $siteTestsDtoArray, $month, $year, $targetPersonId, $group)
    {
        TypeCheck::isCollectionOfClass($siteTestsDtoArray, TesterMultiSitePerformanceDto::class);

        $comparator = function (TesterMultiSitePerformanceDto $a, TesterMultiSitePerformanceDto $b) {
            return $b->getTotal() - $a->getTotal();
        };

        usort($siteTestsDtoArray, $comparator);

        return ArrayUtils::map($siteTestsDtoArray, function (TesterMultiSitePerformanceDto $siteStatsDto) use ($month, $year, $targetPersonId, $group){
            if ($this->contextProvider->isYourProfileContext()) {
                $tqiComponentsAtSiteUrl = ProfileRoutes::of($this->url)->yourProfileTqiComponentsAtSite($siteStatsDto->getSiteId(), $month, $year, $group);
            } elseif ($this->contextProvider->isUserSearchContext()) {
                $tqiComponentsAtSiteUrl = ProfileRoutes::of($this->url)->userSearchTqiComponentsAtSite($targetPersonId, $siteStatsDto->getSiteId(), $month, $year, $group);
            } else {
                $tqiComponentsAtSiteUrl = VtsRoutes::of($this->url)->vtsUserProfileTestQuality($siteStatsDto->getSiteId(), $targetPersonId, $month, $year, $group);
            }

            $addressLine = $siteStatsDto->getSiteAddress()
                ? (new AddressFormatter())
                ->setAddressPartsGlue(', ')
                ->escapedDtoToMultiLine($siteStatsDto->getSiteAddress())
                : "";

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
     * @param string $monthFullName
     * @param string $year
     * @param array $breadcrumbs
     * @return ViewActionResult
     */
    private function buildActionResult($vm, $monthFullName, $year, $breadcrumbs)
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('test-quality-information/view');

        $actionResult->layout()->setPageSubTitle($this->getProfileDescription());
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTitle('Test quality information');
        $actionResult->layout()->setPageLede(sprintf(
                'Tests done at all associated sites in %s %s',
                $monthFullName,
                $year
            )
        );

        return $actionResult;
    }

    private function setYourProfileFlag($context)
    {
        $this->isYourProfile = (ContextProvider::YOUR_PROFILE_CONTEXT === $context);
    }

    private function getProfileDescription()
    {
        return $this->isYourProfile ? self::SUBTITLE_YOUR_PROFILE : self::SUBTITLE_USER_PROFILE;
    }

    private function getReturnLinkText()
    {
        return 'Return to ' . strtolower($this->getProfileDescription());
    }
    private function getMonthFullName(\DateTime $date)
    {
        return $date->format('F');
    }

    /**
     * @param $params
     * @return TestQualityInformationMonthFilter
     */
    private function getMonthFilter($params)
    {
        return new TestQualityInformationMonthFilter($this->personProfileRoutes, $params, $this->url);
    }
}