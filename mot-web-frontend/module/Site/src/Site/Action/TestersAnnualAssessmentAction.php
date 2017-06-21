<?php

namespace Site\Action;

use Core\Action\ViewActionResult;
use DvsaClient\MapperFactory;
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

    private $testersAnnualAssessmentApiResource;
    private $urlHelper;
    private $mapperFactory;
    private $authorisationService;
    private $siteBreadcrumbsBuilder;
    private $testersAnnualAssessmentTable;
    private $mapper;

    public function __construct(
        TestersAnnualAssessmentApiResource $testersAnnualAssessmentApiResource,
        Url $urlHelper,
        MapperFactory $mapperFactory,
        MotAuthorisationServiceInterface $authorisationService,
        MapperFactory $mapper,
        SiteBreadcrumbsBuilder $siteBreadcrumbsBuilder
    )
    {
        $this->testersAnnualAssessmentApiResource = $testersAnnualAssessmentApiResource;
        $this->urlHelper = $urlHelper;
        $this->mapperFactory = $mapperFactory;
        $this->authorisationService = $authorisationService;
        $this->siteBreadcrumbsBuilder = $siteBreadcrumbsBuilder;
        $this->testersAnnualAssessmentTable = new TestersAnnualAssessmentTable();
        $this->mapper = $mapper;
    }

    /**
     * @param $siteId
     * @return ViewActionResult
     */
    public function annualAssessmentCertificatesAction($siteId)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::TESTERS_ANNUAL_ASSESSMENT_VIEW, $siteId);

        $assessmentDto = $this->testersAnnualAssessmentApiResource->getTestersAnnualAssessmentForSite($siteId);
        $site = $this->mapper->Site->getById($siteId);

        return $this->buildActionResult($assessmentDto, $site);
    }

    /**
     * @param TestersAnnualAssessmentDto $assessmentDto
     * @param VehicleTestingStationDto $siteDto
     * @return ViewActionResult
     */
    private function buildActionResult(TestersAnnualAssessmentDto $assessmentDto, VehicleTestingStationDto $siteDto)
    {
        $actionResult = new ViewActionResult();
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setPageSubTitle(self::PAGE_SUB_TITLE);
        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);
        $actionResult->layout()->setBreadcrumbs($this->buildBreadcrumbs($siteDto));
        $actionResult->setViewModel($this->buildViewModel($assessmentDto, $siteDto));

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
     * @return TestersAnnualAssessmentViewModel
     */
    private function buildViewModel(TestersAnnualAssessmentDto $assessmentDto, VehicleTestingStationDto $siteDto)
    {
        $testersAnnualAssessmentViewModel = new TestersAnnualAssessmentViewModel($this->urlHelper);
        $testersAnnualAssessmentViewModel->setVtsId($siteDto->getId());

        if(VtsAuthorisationForTesting::canTestClass1Or2($siteDto->getTestClasses())){
            $tableForGroupA = $this->testersAnnualAssessmentTable
                ->getTableWithAssessments($assessmentDto->getGroupAAssessments(), 'a', $siteDto->getId());
            $testersAnnualAssessmentViewModel->setCanTestGroupA(true);
            $testersAnnualAssessmentViewModel->setTableForGroupA($tableForGroupA);
        }

        if(VtsAuthorisationForTesting::canTestAnyOfClass3AndAbove($siteDto->getTestClasses())){
            $tableForGroupB = $this->testersAnnualAssessmentTable
                ->getTableWithAssessments($assessmentDto->getGroupBAssessments(), 'b', $siteDto->getId());
            $testersAnnualAssessmentViewModel->setCanTestGroupB(true);
            $testersAnnualAssessmentViewModel->setTableForGroupB($tableForGroupB);
        }

        return $testersAnnualAssessmentViewModel;
    }
}