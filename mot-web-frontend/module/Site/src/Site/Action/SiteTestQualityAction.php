<?php

namespace Site\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use DvsaCommon\Constants\FeatureToggle;
use Site\Form\TQIMonthRangeForm;
use Site\Service\CsvFileSizeCalculator;
use Site\ViewModel\TestQuality\SiteTestQualityViewModel;

class SiteTestQualityAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Test quality information';

    private $sitePerformanceApiResource;
    private $nationalPerformanceApiResource;
    private $assertion;
    private $siteMapper;
    private $dateTimeHolder;

    /** @var VehicleTestingStationDto */
    private $site;

    public function __construct(
        SitePerformanceApiResource $sitePerformanceApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        SiteMapper $siteMapper,
        ViewVtsTestQualityAssertion $assertion,
        DateTimeHolder $dateTimeHolder,
        FeatureToggles $featureToggles
    ) {
        $this->sitePerformanceApiResource = $sitePerformanceApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->siteMapper = $siteMapper;
        $this->assertion = $assertion;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->featureToggles = $featureToggles;
    }

    public function execute($siteId, $monthRange, $isReturnToAETQI, array $breadcrumbs)
    {
        $gqr3MonthsViewEnabled = $this->featureToggles->isEnabled(FeatureToggle::GQR_REPORTS_3_MONTHS_OPTION);

        $this->assertion->assertGranted($siteId);
        $monthRangeForm = (new TQIMonthRangeForm($gqr3MonthsViewEnabled))
            ->setValue($monthRange)
            ->setBackTo($isReturnToAETQI);
        if(!$monthRangeForm->isValid($monthRange)){
            return new NotFoundActionResult();
        }

        $breadcrumbs += [self::PAGE_TITLE => null];

        $sitePerformance = $this->sitePerformanceApiResource->getForMonthRange($siteId, $monthRange);
        $nationalPerformance = $this->nationalPerformanceApiResource->getForMonths($monthRange);
        $this->site = $this->siteMapper->getById($siteId);

        return $this->buildActionResult(
            new SiteTestQualityViewModel(
                $sitePerformance,
                $nationalPerformance,
                $this->site,
                (CsvFileSizeCalculator::calculateFileSizeForGroupA(count($sitePerformance->getA()->getStatistics()))),
                (CsvFileSizeCalculator::calculateFileSizeForGroupB(count($sitePerformance->getB()->getStatistics()))),
                $isReturnToAETQI,
                $monthRangeForm,
                $this->dateTimeHolder,
                $monthRange
            ),
            $breadcrumbs
        );
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

        return $actionResult;
    }

    private function getPageTitle()
    {
        return $this->site->getName();
    }
}
