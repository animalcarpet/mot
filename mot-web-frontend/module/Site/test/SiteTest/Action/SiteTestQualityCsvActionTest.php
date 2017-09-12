<?php

namespace SiteTest\Action;

use Core\File\CsvFile;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\Common\ReportStatusDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\SiteComponentFailRateApiResource;
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
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use Site\Action\SiteTestQualityCsvAction;
use Zend\Mvc\Controller\Plugin\Url;

class SiteTestQualityCsvActionTest extends \PHPUnit_Framework_TestCase
{
    const SITE_ID = 1;
    const SITE_NAME = 'name';
    const SITE_NUMBER = 'V1234';
    const MONTHS_RANGE = 1;
    const YEAR = '2015';
    const RETURN_LINK = '/vehicle-testing-station/1';
    const REQUIRED_PERMISSION = PermissionAtSite::VTS_VIEW_TEST_QUALITY;
    const GROUP_CODE = 'A';
    const IS_RETURN_TO_AE_TQI = false;

    private $breadcrumbs = [
        'org' => 'link',
        'vts' => 'link2',
        'Test quality information' => null,
    ];

    /** @var SitePerformanceApiResource */
    private $sitePerformanceApiResourceMock;

    /** @var NationalPerformanceApiResource */
    private $nationalPerformanceApiResourceMock;

    /** @var SiteComponentFailRateApiResource */
    private $siteComponentFailRateApiResourceMock;

    /** @var ComponentFailRateApiResource */
    private $componentFailRateApiResourceMock;

    /** @var NationalComponentStatisticApiResource */
    private $nationalComponentStatisticApiResourceMock;

    /** @var SiteTestQualityCsvAction */
    private $siteTestQualityCsvAction;

    /** @var SiteMapper */
    private $siteMapper;

    /** @var AuthorisationServiceMock */
    private $authorisationService;

    /** @var VehicleTestingStationDto */
    private $siteDto;

    /** @var SitePerformanceDto */
    private $sitePerformanceDto;

    private $url;

    protected function setUp()
    {
        $this->sitePerformanceDto = $this->buildSitePerformanceDto();

        $this->sitePerformanceApiResourceMock = XMock::of(SitePerformanceApiResource::class);
        $this->sitePerformanceApiResourceMock->method('getForMonthRange')
            ->willReturn($this->sitePerformanceDto);

        $this->siteComponentFailRateApiResourceMock = XMock::of(SiteComponentFailRateApiResource::class);
        $this->siteComponentFailRateApiResourceMock->method('get')
            ->willReturn($this->buildSiteComponentStatisticsDto());

        $this->componentFailRateApiResourceMock = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResourceMock->method('getForAllTestersAtSite')
            ->willReturn($this->buildComponentBreakdownDtos());

        $this->nationalComponentStatisticApiResourceMock = XMock::of(NationalComponentStatisticApiResource::class);
        $this->nationalComponentStatisticApiResourceMock->method('getForDate')
            ->willReturn($this->buildNationalComponentStatisticsDto());

        $this->nationalPerformanceApiResourceMock = XMock::of(NationalPerformanceApiResource::class);
        $this->nationalPerformanceApiResourceMock->method('getForMonths')
            ->willReturn($this->buildNationalStatisticsPerformanceDto());

        $this->siteDto = (new VehicleTestingStationDto())
            ->setTestClasses([])
            ->setName(self::SITE_NAME)
            ->setSiteNumber(self::SITE_NUMBER)
            ->setId(1);

        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->siteMapper->expects(
            $this->any())
            ->method('getById')
            ->willReturn($this->siteDto);

        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->grantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, self::SITE_ID);

        $this->siteTestQualityCsvAction = new SiteTestQualityCsvAction(
            $this->sitePerformanceApiResourceMock,
            $this->componentFailRateApiResourceMock,
            $this->siteComponentFailRateApiResourceMock,
            $this->nationalComponentStatisticApiResourceMock,
            $this->nationalPerformanceApiResourceMock,
            $this->siteMapper,
            new ViewVtsTestQualityAssertion($this->authorisationService),
            new TestDateTimeHolder(new \DateTime('2015-02-14'))
        );

        $urlMethods = get_class_methods(Url::class);
        $urlMethods[] = '__invoke';

        $url = XMock::of(Url::class, $urlMethods);
        $url
            ->expects($this->any())
            ->method('__invoke')
            ->willReturn('http://link');

        $this->url = $url;
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAssertionIsChecked()
    {
        // GIVEN I have no permission to view the page
        $this->authorisationService->clearAll();

        // WHEN I try to view it
        $this->siteTestQualityCsvAction->execute(self::SITE_ID, self::MONTHS_RANGE, self::IS_RETURN_TO_AE_TQI, $this->breadcrumbs, $this->url, [], []);
        // THEN I get an exception
    }

    public function testCsvFileIsReturned()
    {
        $result = $this->siteTestQualityCsvAction->execute(self::SITE_ID, self::MONTHS_RANGE, self::GROUP_CODE);

        $this->assertInstanceOf(CsvFile::class, $result->getFile());
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
        $national->setReportStatus((new ReportStatusDto())->setIsCompleted(true));

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

        $stats1->setUsername('Tester');
        $stats1->setTotal(1);
        $stats1->setAverageTime(new TimeSpan(1, 1, 1, 1));
        $stats1->setPercentageFailed(100);

        $stats2 = new EmployeePerformanceDto();

        $stats2->setUsername('Tester 2');
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

        $stats3->setUsername('Tester 3');
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

    private static function buildSiteComponentStatisticsDto():SiteComponentStatisticsDto
    {
        $siteComponentStatisticsDto = new SiteComponentStatisticsDto();
        $siteComponentStatisticsDto
            ->setComponents(self::buildComponents())
            ->setGroup(self::GROUP_CODE)
            ->setSiteId(self::SITE_ID)
            ->setMonthRange(self::MONTHS_RANGE);

        return $siteComponentStatisticsDto;
    }

    /**
     * @return ComponentBreakdownDto[]
     */
    private static function buildComponentBreakdownDtos(): array
    {
        return [
            (new ComponentBreakdownDto())
                ->setComponents(self::buildComponents())
                ->setUserName('tester1'),
            (new ComponentBreakdownDto())
                ->setComponents(self::buildComponents())
                ->setUserName('tester1'),
        ];
    }

    private static function buildNationalComponentStatisticsDto():NationalComponentStatisticsDto
    {
        $nationalComponentStatisticsDto = new NationalComponentStatisticsDto();
        $nationalComponentStatisticsDto
            ->setComponents(self::buildComponents())
            ->setGroup(self::GROUP_CODE)
            ->setMonthRange(self::MONTHS_RANGE);

        return $nationalComponentStatisticsDto;
    }

    /**
     * @return ComponentDto[]
     */
    private static function buildComponents():array
    {
        $components = [];

        $components[] = (new ComponentDto())->setId(1)->setName('RFR1')->setPercentageFailed(0.0);
        $components[] = (new ComponentDto())->setId(2)->setName('RFR2')->setPercentageFailed(10.0);

        return $components;
    }
}
