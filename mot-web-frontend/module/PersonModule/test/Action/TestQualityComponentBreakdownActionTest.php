<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Action;

use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\PersonModule\Action\TestQualityComponentBreakdownAction;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityComponentBreakdownViewModel;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiComponentsBreadcrumbs;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Controller\Plugin\Url;

class TestQualityComponentBreakdownActionTest extends PHPUnit_Framework_TestCase
{
    const MONTH = '03';
    const YEAR = '2016';
    const RETURN_LINK = 'return link';
    const REQUIRED_PERMISSION = PermissionAtSite::VTS_VIEW_TEST_QUALITY;
    const GROUP = VehicleClassGroupCode::CARS_ETC;
    const IS_RETURN_TO_AE_TQI = false;
    const TESTER_ID = 12;

    private $breadcrumbs = [
        'Test quality information' => null,
    ];
    const USER_ID = 105;


    /** @var  ComponentFailRateApiResource */
    protected $componentFailRateApiResource;

    /** @var  NationalComponentStatisticApiResource */
    private $nationalComponentStatisticApiResource;

    /** @var  ContextProvider */
    private $contextProvider;

    /** @var PersonProfileRoutes */
    private $routes;

    /** @var TesterGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;

    /** @var  ViewTesterTestQualityAssertion */
    private $assertion;

    /** @var  Url */
    private $urlPluginMock;

    private $requestParams = [];

    private $testerTqiBreadcrumbs;

    protected function setUp()
    {
        $this->componentFailRateApiResource = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResource->expects($this->any())
            ->method('getForTester')
            ->willReturn($this->buildComponentBreakdownDto());

        $this->nationalComponentStatisticApiResource = XMock::of(NationalComponentStatisticApiResource::class);
        $this->nationalComponentStatisticApiResource->expects($this->any())
            ->method('getForDate')
            ->willReturn($this->buildNationalComponentStatisticsDto());

        $this->contextProvider = XMock::of(ContextProvider::class);

        $this->routes = XMock::of(PersonProfileRoutes::class);

        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->testerGroupAuthorisationMapper->expects($this->once())
            ->method("getAuthorisation")
            ->willReturn(new TesterAuthorisation());

        $this->assertion = XMock::of(ViewTesterTestQualityAssertion::class);
        $this->assertion->expects($this->once())
            ->method("assertGranted")
            ->willReturn(null);

        $this->urlPluginMock = XMock::of(Url::class);
        $this->urlPluginMock->method('fromRoute')
            ->willReturn(self::RETURN_LINK);

        $this->testerTqiBreadcrumbs = XMock::of(TesterTqiComponentsBreadcrumbs::class);
        $this
            ->testerTqiBreadcrumbs
            ->expects($this->any())
            ->method("getBreadcrumbs")
            ->willReturn($this->breadcrumbs);
    }

    public function test404IsReturnedWhenThereIsNoData()
    {
        $this->setUpServiceWithEmptyApiResponse();

        $result = $this->createSut()->execute(
            self::TESTER_ID,
            self::GROUP,
            self::MONTH,
            self::YEAR,
            $this->urlPluginMock,
            $this->requestParams
        );

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    private function setUpServiceWithEmptyApiResponse()
    {
        $this->componentFailRateApiResource = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResource->method('getForTester')
            ->willReturn($this->buildEmptyComponentBreakdownDto());
    }

    public function testValuesArePopulatedToLayoutResult()
    {
        $result = $this->createSut()->execute(
            self::TESTER_ID,
            self::GROUP,
            self::MONTH,
            self::YEAR,
            $this->urlPluginMock,
            $this->requestParams
        );

        /** @var TestQualityComponentBreakdownViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($vm);
        $this->assertNotNull($result->getTemplate());

        $this->assertNotNull($result->layout()->getPageTitle());
        $this->assertNotNull($result->layout()->getPageSubTitle());
        $this->assertNotNull($result->layout()->getTemplate());

        $this->assertSame(
            ["Test quality information" => null],
            $result->layout()->getBreadcrumbs()
        );
    }

    private function buildNationalComponentStatisticsDto()
    {
        $national = new NationalComponentStatisticsDto();
        $national->setMonth(4);
        $national->setYear(2016);

        $national->setComponents($this->buildComponentsDtos());

        return $national;
    }

    private function buildComponentsDtos()
    {
        $brakes = new ComponentDto();
        $brakes->setId(1);
        $brakes->setPercentageFailed(50.123123);
        $brakes->setName('Brakes');

        $tyres = new ComponentDto();
        $tyres->setId(2);
        $tyres->setPercentageFailed(30.5523);
        $tyres->setName('Tyres');

        $userEmpty = new ComponentDto();
        $userEmpty->setId(3);
        $userEmpty->setPercentageFailed(11.12312);
        $userEmpty->setName('Component that is missing in user stats');

        return [$brakes, $tyres, $userEmpty];
    }

    private function buildEmptyComponentBreakdownDto()
    {
        return $this->buildComponentBreakdownDto(0);
    }

    protected function createSut()
    {
        return new TestQualityComponentBreakdownAction(
            $this->componentFailRateApiResource,
            $this->nationalComponentStatisticApiResource,
            $this->contextProvider,
            $this->routes,
            $this->testerGroupAuthorisationMapper,
            $this->assertion,
            $this->testerTqiBreadcrumbs
        );
    }

    private function buildComponentBreakdownDto($total = 1)
    {
        $dto = (new ComponentBreakdownDto())
            ->setComponents($this->buildComponentsDtos())
            ->setGroupPerformance(
                (new MotTestingPerformanceDto())
                    ->setTotal($total)
            );

        return $dto;
    }
}