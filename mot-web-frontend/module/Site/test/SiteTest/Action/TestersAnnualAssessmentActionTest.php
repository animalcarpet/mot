<?php

namespace SiteTest\Action;

use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem;
use DvsaCommon\ApiClient\Site\Dto\TestersAnnualAssessmentDto;
use DvsaCommon\ApiClient\Site\TestersAnnualAssessmentApiResource;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit\Framework\TestCase;
use Report\Table\Table;
use Site\Action\TestersAnnualAssessmentAction;
use Site\Service\SiteBreadcrumbsBuilder;
use Site\ViewModel\TestersAnnualAssessmentViewModel;
use Zend\View\Helper\Url;

class TestersAnnualAssessmentActionTest extends TestCase
{
    const SITE_ID = self::VTS_ID;
    const VTS_ID = 10;

    /** @var  Url | \PHPUnit_Framework_MockObject_MockObject  */
    private $urlHelper;
    /** @var  SiteBreadcrumbsBuilder | \PHPUnit_Framework_MockObject_MockObject  */
    private $siteBreadcrumbsBuilder;
    /** @var  TestersAnnualAssessmentApiResource | \PHPUnit_Framework_MockObject_MockObject  */
    private $testersAnnualAssessmentApiResource;
    /** @var  SiteMapper | \PHPUnit_Framework_MockObject_MockObject */
    private $siteMapper;
    /** @var  AuthorisationServiceMock */
    private $authorisationService;
    /** @var  TestersAnnualAssessmentAction */
    private $action;

    public function setUp()
    {
        $this->authorisationService = new AuthorisationServiceMock();
        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->testersAnnualAssessmentApiResource = XMock::of(TestersAnnualAssessmentApiResource::class);
        $this->siteBreadcrumbsBuilder = XMock::of(SiteBreadcrumbsBuilder::class);
        $this->urlHelper = XMock::of(Url::class);

        $this->action = new TestersAnnualAssessmentAction(
            $this->testersAnnualAssessmentApiResource,
            $this->urlHelper,
            $this->siteMapper,
            $this->authorisationService,
            $this->siteBreadcrumbsBuilder
        );
    }

    public function testLackOfPermissions()
    {
        $this->expectException(UnauthorisedException::class);
        $this->action->annualAssessmentCertificatesAction(self::SITE_ID, '');
    }

    public function testHappyPath()
    {
        $this->setupMocks();
        $this->testersAnnualAssessmentApiResource->expects($this->once())->method("getTestersAnnualAssessmentForSite")
            ->willReturn(new TestersAnnualAssessmentDto());
        $this->siteMapper->method("getById")->willReturn((new VehicleTestingStationDto())->setId(self::SITE_ID));
        $this->urlHelper->method("__invoke")->willReturn('returnLink');

        $result = $this->action->annualAssessmentCertificatesAction(self::SITE_ID, '');
        $this->assertEquals("Vehicle Testing Station", $result->layout()->getPageSubTitle());
        $this->assertEquals("Tester annual assessments", $result->layout()->getPageTitle());
        $this->assertEquals(["Tester annual assessments" => null], $result->layout()->getBreadcrumbs());
        /** @var TestersAnnualAssessmentViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertEquals('returnLink', $viewModel->getBackLink());
    }

    /**
     * @dataProvider tableADataProvider
     */
    public function testGroupATable($groupATestClass)
    {
        $this->setupMocks();
        $site = new VehicleTestingStationDto();
        $site->setTestClasses([$groupATestClass]);
        $assessment = new GroupAssessmentListItem();
        $assessmentDto = new TestersAnnualAssessmentDto();
        $assessmentDto->setGroupAAssessments([$assessment]);

        $this->siteMapper->expects($this->once())->method("getById")->with(self::SITE_ID)->willReturn($site);
        $this->testersAnnualAssessmentApiResource->expects($this->once())->method("getTestersAnnualAssessmentForSite")
            ->willReturn($assessmentDto);

        $result = $this->action->annualAssessmentCertificatesAction(self::SITE_ID, '');
        /** @var TestersAnnualAssessmentViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertFalse($viewModel->getCanTestGroupB());
        $this->assertInstanceOf(Table::class, $viewModel->getTableForGroupA());
    }

    /**
     * @dataProvider tableBDataProvider
     */
    public function testGroupBTable($groupBTestClass)
    {
        $this->setupMocks();

        $site = new VehicleTestingStationDto();
        $site->setTestClasses([$groupBTestClass]);

        $assessment = new GroupAssessmentListItem();
        $assessmentDto = new TestersAnnualAssessmentDto();
        $assessmentDto->setGroupBAssessments([$assessment]);

        $this->siteMapper->expects($this->once())->method("getById")->with(self::SITE_ID)->willReturn($site);
        $this->testersAnnualAssessmentApiResource->expects($this->once())->method("getTestersAnnualAssessmentForSite")
            ->willReturn($assessmentDto);

        $result = $this->action->annualAssessmentCertificatesAction(self::SITE_ID, '');
        /** @var TestersAnnualAssessmentViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertFalse($viewModel->getCanTestGroupA());
        $this->assertTrue($viewModel->getCanTestGroupB());
        $this->assertInstanceOf(Table::class, $viewModel->getTableForGroupB());
    }

    public function tableADataProvider():array
    {
        return [
            [VehicleClassCode::CLASS_1], [VehicleClassCode::CLASS_2],
        ];
    }

    public function tableBDataProvider():array
    {
        return [
            [VehicleClassCode::CLASS_3], [VehicleClassCode::CLASS_4], [VehicleClassCode::CLASS_5], [VehicleClassCode::CLASS_7],
        ];
    }

    private function setupMocks()
    {
        $this->siteBreadcrumbsBuilder->expects($this->once())->method("buildBreadcrumbs")->willReturn([]);
        $this->authorisationService->grantedAtSite(PermissionAtSite::TESTERS_ANNUAL_ASSESSMENT_VIEW, self::VTS_ID);
    }
}