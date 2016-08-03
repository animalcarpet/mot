<?php

namespace SiteTest\Action;

use Core\File\CsvFile;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Site\Action\SiteTestQualityAction;
use Site\ViewModel\TestQuality\SiteTestQualityViewModel;

class SiteTestQualityActionTest extends \PHPUnit_Framework_TestCase
{
    const SITE_ID = 1;
    const SITE_NAME = 'name';
    const MONTH = '04';
    const YEAR = '2015';
    const RETURN_LINK = '/vehicle-testing-station/1';
    const REQUIRED_PERMISSION = PermissionAtSite::VTS_VIEW_TEST_QUALITY;
    const GROUP_CODE = 'A';
    const IS_RETURN_TO_AE_TQI = false;

    private $breadcrumbs = [
        'org'                      => 'link',
        'vts'                      => 'link2',
        'Test quality information' => null,
    ];

    /** @var  SitePerformanceApiResource */
    private $sitePerformanceApiResourceMock;

    /** @var  NationalPerformanceApiResource */
    private $nationalPerformanceApiResourceMock;

    /** @var  SiteTestQualityAction */
    private $siteTestQualityAction;

    /** @var SiteMapper */
    private $siteMapper;

    /** @var AuthorisationServiceMock */
    private $authorisationService;

    /** @var VehicleTestingStationDto */
    private $siteDto;

    /** @var SitePerformanceDto */
    private $sitePerformanceDto;

    protected function setUp()
    {
        $this->sitePerformanceDto = $this->buildSitePerformanceDto();

        $this->sitePerformanceApiResourceMock = XMock::of(SitePerformanceApiResource::class);
        $this->sitePerformanceApiResourceMock->method('getForDate')
            ->willReturn($this->sitePerformanceDto);

        $this->nationalPerformanceApiResourceMock = XMock::of(NationalPerformanceApiResource::class);
        $this->nationalPerformanceApiResourceMock->method('getForDate')
            ->willReturn($this->buildNationalStatisticsPerformanceDto());

        $this->siteDto = (new VehicleTestingStationDto())
            ->setTestClasses([])
            ->setName(self::SITE_NAME)
            ->setId(1);

        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->siteMapper->expects(
            $this->any())
            ->method('getById')
            ->willReturn($this->siteDto);

        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->grantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, self::SITE_ID);

        $this->siteTestQualityAction = new SiteTestQualityAction(
            $this->sitePerformanceApiResourceMock,
            $this->nationalPerformanceApiResourceMock,
            $this->siteMapper,
            new ViewVtsTestQualityAssertion($this->authorisationService)
        );
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAssertionIsChecked()
    {
        // GIVEN I have no permission to view the page
        $this->authorisationService->clearAll();

        // WHEN I try to view it
        $this->siteTestQualityAction->execute(self::SITE_ID, self::MONTH, self::YEAR, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs);
        // THEN I get an exception
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAssertionIsCheckedForCsv()
    {
        // GIVEN I have no permission to view the page
        $this->authorisationService->clearAll();

        //WHEN I try to download CSV
        $this->siteTestQualityAction->getCsv(self::SITE_ID, self::MONTH, self::YEAR, self::GROUP_CODE);
        //THEN I get an exception
    }

    public function testValuesArePopulatedToLayoutResult()
    {
        $result = $this->siteTestQualityAction->execute(self::SITE_ID, self::MONTH, self::YEAR, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs);

        /** @var SiteTestQualityViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($vm);
        $this->assertNotNull($result->getTemplate());

        $this->assertSame(self::SITE_NAME, $result->layout()->getPageTitle());
        $this->assertNotNull($result->layout()->getPageSubTitle());
        $this->assertNotNull($result->layout()->getTemplate());
        $this->assertNotNull($result->layout()->getPageTertiaryTitle());

        $this->assertSame(['breadcrumbs' => $this->breadcrumbs], $result->layout()->getBreadcrumbs());
    }

    public function testCsvFileIsReturned()
    {
        $result = $this->siteTestQualityAction->getCsv(self::SITE_ID, self::MONTH, self::YEAR, self::GROUP_CODE);

        $this->assertInstanceOf(CsvFile::class, $result->getFile());
    }

    /**
     * @dataProvider siteClassesDataProvider
     *
     * @param $vehicleClasses
     * @param $vehicleGroup
     */
    public function testGroupSectionIsShownWhenSiteIsAuthorisedForTheGroup($vehicleClasses, $vehicleGroup)
    {
        // GIVEN site is allowed to test specific classes of vehicles
        $this->siteDto->setTestClasses($vehicleClasses);

        // AND it doesn't have any tests
        $this->setUpTotalTestsDoneInSite(0, 0);

        // WHEN I view the statistics
        $result = $this->siteTestQualityAction->execute(self::SITE_ID, self::MONTH, self::YEAR, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs);

        /** @var SiteTestQualityViewModel $viewModel */
        $viewModel = $result->getViewModel();

        // THEN I can see the given group section
        $this->assertTrue($viewModel->canGroupSectionBeViewed($vehicleGroup));
    }

    /**
     * @dataProvider siteClassesDataProvider
     *
     * @param $vehicleClasses
     * @param $vehicleGroup
     */
    public function testGroupSectionIsVisibleWhenSiteIsAuthorisedForTheGroup($vehicleClasses, $vehicleGroup)
    {
        // GIVEN site isn't allowed to test any classes of vehicles
        $this->siteDto->setTestClasses($vehicleClasses);;

        // AND it has tests in the given month
        $groupATests = $vehicleGroup === VehicleClassGroupCode::BIKES ? 10 : 0;
        $groupBTests = $vehicleGroup === VehicleClassGroupCode::CARS_ETC ? 10 : 0;
        $this->setUpTotalTestsDoneInSite($groupATests, $groupBTests);

        // WHEN I view the statistics
        $result = $this->siteTestQualityAction->execute(self::SITE_ID, self::MONTH, self::YEAR, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs);

        /** @var SiteTestQualityViewModel $viewModel */
        $viewModel = $result->getViewModel();

        // THEN I can see the given group section
        $this->assertTrue($viewModel->canGroupSectionBeViewed($vehicleGroup));
    }

    /**
     * @dataProvider siteClassesDataProvider
     *
     * @param $vehicleClasses
     * @param $vehicleGroup
     */
    public function testGroupSectionsIsHiddenWhenThereAreNoTestsAndSiteIsNotAuthorised($vehicleClasses, $vehicleGroup)
    {
        // GIVEN site is authorised to do tests for the other group
        $otherGroup = $vehicleGroup === VehicleClassGroupCode::BIKES ? VehicleClassGroupCode::CARS_ETC : VehicleClassGroupCode::BIKES;
        $vehicleClasses = VehicleClassGroup::getClassesForGroup($otherGroup);

        $this->siteDto->setTestClasses($vehicleClasses);

        // AND and it has no tests done what so ever
        $this->setUpTotalTestsDoneInSite(0, 0);

        // WHEN I view the statistics
        $result = $this->siteTestQualityAction->execute(self::SITE_ID, self::MONTH, self::YEAR, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs);

        /** @var SiteTestQualityViewModel $viewModel */
        $viewModel = $result->getViewModel();

        // THEN I CAN'T see the given group section
        $this->assertFalse($viewModel->canGroupSectionBeViewed($vehicleGroup));
    }

    public function testBothSectionsAreShownWhenThereIsNoDataAtAll()
    {
        // GIVEN site isn't allowed to test any classes of vehicles
        $this->siteDto->setTestClasses([]);

        // AND and it has no tests done
        $this->setUpTotalTestsDoneInSite(0, 0);

        // WHEN I view the statistics
        $result = $this->siteTestQualityAction->execute(self::SITE_ID, self::MONTH, self::YEAR, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs);

        /** @var SiteTestQualityViewModel $viewModel */
        $viewModel = $result->getViewModel();

        // THEN I see both sections
        $this->assertTrue($viewModel->canGroupSectionBeViewed(VehicleClassGroupCode::BIKES));
        $this->assertTrue($viewModel->canGroupSectionBeViewed(VehicleClassGroupCode::CARS_ETC));
    }

    public function siteClassesDataProvider()
    {
        return [
            [[1, 2], VehicleClassGroupCode::BIKES],
            [[3, 4, 5, 7], VehicleClassGroupCode::CARS_ETC],
        ];
    }

    private function setUpTotalTestsDoneInSite($numberOfGroupATest, $numberOfGroupBTest)
    {
        $this->sitePerformanceDto->getA()->getTotal()->setTotal($numberOfGroupATest);
        $this->sitePerformanceDto->getB()->getTotal()->setTotal($numberOfGroupBTest);
    }

    private function buildNationalStatisticsPerformanceDto()
    {
        $national = new NationalPerformanceReportDto();

        $national->setMonth(4);
        $national->setYear(2016);

        $groupA = new MotTestingPerformanceDto();
        $groupA->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $groupA->setPercentageFailed(50);
        $groupA->setTotal(10);

        $national->setGroupA($groupA);

        $groupB = new MotTestingPerformanceDto();
        $groupB->setAverageTime(new TimeSpan(0, 0, 2, 2));
        $groupB->setPercentageFailed(30);
        $groupB->setTotal(5);

        $national->setGroupB($groupB);

        return $national;
    }

    private function buildSitePerformanceDto()
    {
        $site = new SitePerformanceDto();

        $groupA = new SiteGroupPerformanceDto();

        $stats1 = new EmployeePerformanceDto();

        $stats1->setUsername("Tester");
        $stats1->setTotal(1);
        $stats1->setAverageTime(new TimeSpan(1, 1, 1, 1));
        $stats1->setPercentageFailed(100);

        $stats2 = new EmployeePerformanceDto();

        $stats2->setUsername("Tester 2");
        $stats2->setTotal(2);
        $stats2->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $stats2->setPercentageFailed(50.00);

        $groupA->setStatistics([$stats1, $stats2]);

        $totalA = (new MotTestingPerformanceDto())
            ->setAverageTime(new TimeSpan(2, 2, 2, 2))
            ->setTotal(2000)
            ->setPercentageFailed(10.10);

        $groupA->setTotal($totalA);

        $groupB = new SiteGroupPerformanceDto();

        $stats3 = new EmployeePerformanceDto();

        $stats3->setUsername("Tester 3");
        $stats3->setTotal(200);
        $stats3->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $stats3->setPercentageFailed(33.33);

        $groupB->setStatistics([$stats3]);

        $totalB = (new MotTestingPerformanceDto())
            ->setAverageTime(new TimeSpan(2, 2, 2, 2))
            ->setTotal(400)
            ->setPercentageFailed(0);

        $groupB->setTotal($totalB);

        $site->setA($groupA);
        $site->setB($groupB);

        return $site;
    }
}