<?php

namespace OrganisationTest\Action;

use Core\Action\NotFoundActionResult;
use Core\Action\ViewActionResult;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\AuthorisedExaminerSitePerformanceApiResource;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Action\TestQualityInformationAction;
use DvsaCommon\Configuration\MotConfig;
use Organisation\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use Zend\View\Helper\Url;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class TestQualityInformationActionTest extends AbstractFrontendControllerTestCase
{
    const ORGANISATION_NAME = 'name';
    const ORGANISATION_ID = 123;
    const PAGE_NUMBER = 1;
    const LINK = '/authorised-examiner/1/test-quality-information';

    /** @var OrganisationMapper */
    private $organisationMapper;

    /** @var AuthorisedExaminerSitePerformanceApiResource */
    private $authorisedExaminerSitePerformanceApiResourceMock;

    private $config;

    /** @var  TestQualityInformationAction */
    private $testQualityInformationAction;

    /** @var  Url */
    private $urlPluginMock;

    private $breadcrumbs = [
        self::ORGANISATION_NAME => self::LINK,
        TestQualityInformationAction::PAGE_SUBTITLE => '',
    ];


    protected function setUp()
    {
        $organisationDto = (new OrganisationDto())
            ->setId(1)
            ->setName(self::ORGANISATION_NAME);

        $this->organisationMapper = XMock::of(OrganisationMapper::class);
        $this->organisationMapper->expects(
            $this->any())
            ->method('getAuthorisedExaminer')
            ->willReturn($organisationDto);

        $this->authorisedExaminerSitePerformanceApiResourceMock =
            XMock::of(AuthorisedExaminerSitePerformanceApiResource::class);

        $this->authorisedExaminerSitePerformanceApiResourceMock
            ->method('getData')
            ->willReturn($this->buildAuthorisedExaminerSitePerformanceDto());


        $this->mockConfig();

        $this->urlPluginMock = XMock::of(Url::class);
        $this->urlPluginMock
            ->expects($this->any())
            ->method("__invoke")
            ->willReturn(self::LINK);

        $this->testQualityInformationAction = new TestQualityInformationAction(
            $this->organisationMapper,
            $this->authorisedExaminerSitePerformanceApiResourceMock,
            $this->config,
            $this->urlPluginMock
        );
    }

    /** @dataProvider dataProviderPageNumber
     * @param $pageNumber
     * @param $result
     */
    public function testPageNumberNotCorrect($pageNumber, $result)
    {
        $output = $this->testQualityInformationAction->execute(self::ORGANISATION_ID, $pageNumber);

        $this->assertInstanceOf($result, $output);
    }

    public function dataProviderPageNumber()
    {
        return [
            [
                'pageNumber' => 0,
                'result' => NotFoundActionResult::class,
            ],
            [
                'pageNumber' => 1,
                'result' => ViewActionResult::class,
            ],
        ];
    }

    public function testLayoutForEmptyDto()
    {
        $tqiAction = $this->getEmptyTestQualityInformationAction();
        $result = $tqiAction->execute(self::ORGANISATION_ID, self::PAGE_NUMBER);

        /** @var TestQualityInformationViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($vm);

        $this->assertSame(TestQualityInformationAction::NO_SITES, $result->layout()->getPageTitle());
        $this->assertNotNull($result->layout()->getPageSubTitle());
        $this->assertNotNull($result->layout()->getTemplate());

        $this->assertSame($this->breadcrumbs, $result->layout()->getBreadcrumbs());

    }

    public function testLayoutForSiteAssertedDto()
    {

        $tqiAction = $this->testQualityInformationAction;
        $result = $tqiAction->execute(self::ORGANISATION_ID, self::PAGE_NUMBER);

        /** @var TestQualityInformationViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($vm);

        $this->assertSame(TestQualityInformationAction::PAGE_TITLE, $result->layout()->getPageTitle());
        $this->assertNotNull($result->layout()->getPageSubTitle());
        $this->assertNotNull($result->layout()->getTemplate());

        $this->assertSame($this->breadcrumbs, $result->layout()->getBreadcrumbs());

    }

    private function getEmptyTestQualityInformationAction()
    {
        /** @var AuthorisedExaminerSitePerformanceApiResource|MockObj $authorisedExaminerSitePerformanceApiResourceMock */
        $authorisedExaminerSitePerformanceApiResourceMock =
            XMock::of(AuthorisedExaminerSitePerformanceApiResource::class);
        $authorisedExaminerSitePerformanceApiResourceMock->method('getData')
            ->willReturn((new AuthorisedExaminerSitesPerformanceDto())
                ->setSiteTotalCount(0)
                ->setSites([]));

        return new TestQualityInformationAction(
            $this->organisationMapper,
            $authorisedExaminerSitePerformanceApiResourceMock,
            $this->config,
            $this->urlPluginMock
        );
    }

    public function buildAuthorisedExaminerSitePerformanceDto()
    {
        $authorisedExaminerSitePerformanceDto = new AuthorisedExaminerSitesPerformanceDto();

        $address = (new AddressDto())
            ->setAddressLine1('addressLine1')
            ->setCountry('Country')
            ->setPostcode('Postcode')
            ->setTown('Town');

        $siteDto = (new SiteDto())
            ->setId(1)
            ->setAddress($address)
            ->setName('SiteName')
            ->setNumber('SiteNumber')
            ->setRiskAssessmentScore(1.3);

        $authorisedExaminerSitePerformanceDto
            ->setSites([$siteDto])
            ->setSiteTotalCount(1);

        return $authorisedExaminerSitePerformanceDto;
    }


    protected function mockConfig()
    {
        $this->config = $this->getMockBuilder(MotConfig::class)->disableOriginalConstructor()->getMock();
        $returnMap = [
            ["site_assessment", "green", "start", 0],
            ["site_assessment", "amber", "start", 324.11],
            ["site_assessment", "red", "start", 459.21]
        ];

        $this->config->expects($this->any())->method("get")->will($this->returnValueMap($returnMap));
    }
}