<?php

namespace Site\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Form\TQIMonthRangeForm;
use Site\ViewModel\TestQuality\SiteTestQualityViewModel;
use Zend\Mvc\Controller\Plugin\Url;

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
        DateTimeHolder $dateTimeHolder
    ) {
        $this->sitePerformanceApiResource = $sitePerformanceApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->siteMapper = $siteMapper;
        $this->assertion = $assertion;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function execute($siteId, $monthRange, $isReturnToAETQI, array $breadcrumbs)
    {
        $this->assertion->assertGranted($siteId);
        $monthRangeForm = (new TQIMonthRangeForm())
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
