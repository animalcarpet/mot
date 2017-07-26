<?php

namespace Site\Action;

use Core\Action\ViewActionResult;
use Core\BackLink\BackLinkQueryParam;
use Core\Routing\AeRoutes;
use Core\Routing\VtsRoutes;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Site\Dto\TestersAnnualAssessmentDto;
use DvsaCommon\ApiClient\Site\TestersAnnualAssessmentApiResource;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Authorization\VtsAuthorisationForTesting;
use Site\Service\SiteBreadcrumbsBuilder;
use Site\Table\TestersAnnualAssessmentTable;
use Site\ViewModel\TestersAnnualAssessmentViewModel;
use Zend\View\Helper\Url;

class TestersAnnualAssessmentAction implements AutoWireableInterface
{
    const PAGE_SUB_TITLE = 'Vehicle Testing Station';
    const PAGE_TITLE = 'Tester annual assessments';
    const BACK_LINK_VTS_TEXT = 'Return to Vehicle Testing Station';
    const BACK_LINK_SERVICE_REPORTS_TEXT = 'Return to service reports';

    private $testersAnnualAssessmentApiResource;
    private $urlHelper;
    private $authorisationService;
    private $siteBreadcrumbsBuilder;
    private $testersAnnualAssessmentTable;
    private $siteMapper;

    public function __construct(
        TestersAnnualAssessmentApiResource $testersAnnualAssessmentApiResource,
        Url $urlHelper,
        SiteMapper $siteMapper,
        MotAuthorisationServiceInterface $authorisationService,
        SiteBreadcrumbsBuilder $siteBreadcrumbsBuilder
    )
    {
        $this->testersAnnualAssessmentApiResource = $testersAnnualAssessmentApiResource;
        $this->urlHelper = $urlHelper;
        $this->siteMapper = $siteMapper;
        $this->authorisationService = $authorisationService;
        $this->siteBreadcrumbsBuilder = $siteBreadcrumbsBuilder;
        $this->testersAnnualAssessmentTable = new TestersAnnualAssessmentTable();
    }

    /**
     * @param $siteId
     * @param string $backTo
     * @return ViewActionResult
     */
    public function annualAssessmentCertificatesAction($siteId, $backTo)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::TESTERS_ANNUAL_ASSESSMENT_VIEW, $siteId);

        $assessmentDto = $this->testersAnnualAssessmentApiResource->getTestersAnnualAssessmentForSite($siteId);
        $site = $this->siteMapper->getById($siteId);

        return $this->buildActionResult($assessmentDto, $site, $backTo);
    }

    /**
     * @param TestersAnnualAssessmentDto $assessmentDto
     * @param VehicleTestingStationDto $siteDto
     * @param string $backTo
     * @return ViewActionResult
     */
    private function buildActionResult(TestersAnnualAssessmentDto $assessmentDto, VehicleTestingStationDto $siteDto, $backTo)
    {
        $actionResult = new ViewActionResult();
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setPageSubTitle(self::PAGE_SUB_TITLE);
        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);
        $actionResult->layout()->setBreadcrumbs($this->buildBreadcrumbs($siteDto));
        $actionResult->setViewModel($this->buildViewModel($assessmentDto, $siteDto, $backTo));

        return $actionResult;
    }

    /**
     * @param VehicleTestingStationDto $siteDto
     * @return array
     */
    private function buildBreadcrumbs(VehicleTestingStationDto $siteDto)
    {
        return $this->siteBreadcrumbsBuilder->buildBreadcrumbs($siteDto) + [self::PAGE_TITLE => null];
    }

    /**
     * @param TestersAnnualAssessmentDto $assessmentDto
     * @param VehicleTestingStationDto $siteDto
     * @param string $backTo
     * @return TestersAnnualAssessmentViewModel
     */
    private function buildViewModel(TestersAnnualAssessmentDto $assessmentDto, VehicleTestingStationDto $siteDto, $backTo)
    {
        $testersAnnualAssessmentViewModel = new TestersAnnualAssessmentViewModel($this->urlHelper);
        $testersAnnualAssessmentViewModel->setVtsId($siteDto->getId());

        if(VtsAuthorisationForTesting::canTestClass1Or2($siteDto->getTestClasses())){
            $tableForGroupA = $this->testersAnnualAssessmentTable
                ->getTableWithAssessments($assessmentDto->getGroupAAssessments(), 'a', $siteDto->getId(), $backTo);
            $testersAnnualAssessmentViewModel->setCanTestGroupA(true);
            $testersAnnualAssessmentViewModel->setTableForGroupA($tableForGroupA);
        }

        if(VtsAuthorisationForTesting::canTestAnyOfClass3AndAbove($siteDto->getTestClasses())){
            $tableForGroupB = $this->testersAnnualAssessmentTable
                ->getTableWithAssessments($assessmentDto->getGroupBAssessments(), 'b', $siteDto->getId(), $backTo);
            $testersAnnualAssessmentViewModel->setCanTestGroupB(true);
            $testersAnnualAssessmentViewModel->setTableForGroupB($tableForGroupB);
        }

        if ($backTo === BackLinkQueryParam::SERVICE_REPORTS) {
            $testersAnnualAssessmentViewModel->setBackLink(AeRoutes::of($this->urlHelper)->aeServiceReports($siteDto->getOrganisation()->getId()));
            $testersAnnualAssessmentViewModel->setBackLinkText(self::BACK_LINK_SERVICE_REPORTS_TEXT);
        } else {
            $testersAnnualAssessmentViewModel->setBackLink(VtsRoutes::of($this->urlHelper)->vts($siteDto->getId()));
            $testersAnnualAssessmentViewModel->setBackLinkText(self::BACK_LINK_VTS_TEXT);
        }

        return $testersAnnualAssessmentViewModel;
    }
}