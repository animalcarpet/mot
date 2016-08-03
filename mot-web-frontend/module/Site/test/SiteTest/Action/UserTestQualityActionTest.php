<?php

namespace SiteTest\Action;

use Core\Action\NotFoundActionResult;
use Core\File\CsvFile;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Site\Action\UserTestQualityAction;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;
use SiteTest\ViewModel\SiteTestQualityViewModelTest;
use SiteTest\ViewModel\UserTestQualityViewModelTest;
use Zend\Mvc\Controller\Plugin\Url;

class UserTestQualityActionTest extends PHPUnit_Framework_TestCase
{
    const SITE_ID = 1;
    const SITE_NAME = 'name';
    const MONTH = '03';
    const YEAR = '2016';
    const RETURN_LINK = '/vehicle-testing-station/1/test-quality';
    const REQUIRED_PERMISSION = PermissionAtSite::VTS_VIEW_TEST_QUALITY;
    const GROUP = 'A';
    const IS_RETURN_TO_AE_TQI = false;

    private $breadcrumbs = [
        'Test quality information' => null,
    ];
    const USER_ID = 105;


    /** @var  ComponentFailRateApiResource */
    protected $componentFailRateApiResource;

    /** @var  NationalComponentStatisticApiResource */
    private $nationalComponentStatisticApiResource;

    /** @var  UserTestQualityAction */
    private $userTestQualityAction;

    /** @var  ViewVtsTestQualityAssertion */
    private $assertion;

    /** @var  Url */
    private $urlPluginMock;

    /** @var  AuthorisationServiceMock */
    private $authorisationServiceMock;

    /** @var SiteMapper */
    private $siteMapper;

    /** @var VehicleTestingStationDto */
    private $siteDto;
    /** @var  NationalPerformanceApiResource */
    private $nationalPerformanceApiResourceMock;

    protected function setUp()
    {
        $this->componentFailRateApiResource = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResource->expects($this->any())
            ->method('getForDate')
            ->willReturn(UserTestQualityViewModelTest::buildUserPerformanceDto());

        $this->nationalComponentStatisticApiResource = XMock::of(NationalComponentStatisticApiResource::class);
        $this->nationalComponentStatisticApiResource->expects($this->any())
            ->method('getForDate')
            ->willReturn(UserTestQualityViewModelTest::buildNationalComponentStatisticsDto());

        $this->nationalPerformanceApiResourceMock = XMock::of(NationalPerformanceApiResource::class);
        $this->nationalPerformanceApiResourceMock->expects($this->any())
            ->method('getForDate')
            ->willReturn(SiteTestQualityViewModelTest::buildNationalStatisticsPerformanceDto());

        $this->authorisationServiceMock = new AuthorisationServiceMock();
        $this->authorisationServiceMock->grantedAtSite(self::REQUIRED_PERMISSION, self::SITE_ID);

        $this->assertion = new ViewVtsTestQualityAssertion($this->authorisationServiceMock);

        $this->urlPluginMock = XMock::of(Url::class);
        $this->urlPluginMock->method('fromRoute')
            ->willReturn(self::RETURN_LINK);

        $this->siteDto = (new VehicleTestingStationDto())
            ->setTestClasses([])
            ->setName(self::SITE_NAME);

        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->siteMapper->expects(
            $this->any())
            ->method('getById')
            ->willReturn($this->siteDto);

        $this->userTestQualityAction = new UserTestQualityAction(
            $this->componentFailRateApiResource,
            $this->nationalComponentStatisticApiResource,
            $this->nationalPerformanceApiResourceMock,
            $this->assertion,
            $this->siteMapper
        );
    }

    public function testAssertionIsChecked()
    {
        $this->authorisationServiceMock->clearAll();
        $this->setExpectedException(UnauthorisedException::class);

        $this->userTestQualityAction->execute(self::SITE_ID, self::USER_ID, self::MONTH, self::YEAR,
            VehicleClassGroupCode::BIKES, $this->breadcrumbs, self::IS_RETURN_TO_AE_TQI, $this->urlPluginMock);
    }

    public function test404IsReturnedWhenThereAreNoData()
    {
        $this->setUpServiceWithEmptyApiResponse();

        $result = $this->userTestQualityAction->execute(self::SITE_ID, self::USER_ID, self::MONTH, self::YEAR,
            VehicleClassGroupCode::CARS_ETC, $this->breadcrumbs, self::IS_RETURN_TO_AE_TQI, $this->urlPluginMock);
        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    private function setUpServiceWithEmptyApiResponse()
    {
        $this->componentFailRateApiResource = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResource->method('getForDate')
            ->willReturn(UserTestQualityViewModelTest::buildEmptyGroupPerformance());

        $this->userTestQualityAction = new UserTestQualityAction(
            $this->componentFailRateApiResource,
            $this->nationalComponentStatisticApiResource,
            $this->nationalPerformanceApiResourceMock,
            $this->assertion,
            $this->siteMapper
        );
    }

    public function testValuesArePopulatedToLayoutResult()
    {
        $result = $this->userTestQualityAction->execute(self::SITE_ID, self::USER_ID, self::MONTH, self::YEAR,
            VehicleClassGroupCode::BIKES, $this->breadcrumbs, self::IS_RETURN_TO_AE_TQI, $this->urlPluginMock);

        /** @var UserTestQualityViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($vm);
        $this->assertNotNull($result->getTemplate());

        $this->assertNotNull($result->layout()->getPageTitle());
        $this->assertNotNull($result->layout()->getPageSubTitle());
        $this->assertNotNull($result->layout()->getTemplate());

        $this->assertSame(
            ['breadcrumbs' => $this->breadcrumbs + [UserTestQualityViewModelTest::DISPLAY_NAME => null]],
            $result->layout()->getBreadcrumbs()
        );
    }

    public function testCsvFileIsReturned()
    {
        $result = $this->userTestQualityAction->getCsv(self::SITE_ID, self::USER_ID, self::MONTH, self::YEAR, self::GROUP);

        $this->assertInstanceOf(CsvFile::class, $result->getFile());
    }
}