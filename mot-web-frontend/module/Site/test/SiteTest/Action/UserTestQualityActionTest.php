<?php

namespace SiteTest\Action;

use Core\Action\NotFoundActionResult;
use CoreTest\TestUtils\Identity\FrontendIdentityProviderStub;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\SiteComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Security\RolesMapDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Site\Action\UserTestQualityAction;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Mvc\Router\Http\RouteMatch;
use DvsaFeature\FeatureToggles;

class UserTestQualityActionTest extends PHPUnit_Framework_TestCase
{
    const SITE_ID = 1;
    const SITE_NAME = 'name';
    const THREE_MONTHS_RANGE = 3;
    const ONE_MONTH_RANGE = 1;
    const YEAR = '2016';
    const RETURN_LINK = '/vehicle-testing-station/1/test-quality';
    const REQUIRED_PERMISSION = PermissionAtSite::VTS_VIEW_TEST_QUALITY;
    const GROUP = 'A';
    const IS_RETURN_TO_AE_TQI = false;
    const COMPONENT_ONE_ID = 1;
    const COMPONENT_TWO_ID = 2;
    const COMPONENT_USER_EMPTY_ID = 3;
    const USERNAME = 'tester';
    const DISPLAY_NAME = 'John Smith';

    private $breadcrumbs = [
        'Test quality information' => null,
    ];
    const USER_ID = 105;

    /** @var ComponentFailRateApiResource */
    protected $componentFailRateApiResource;

    /** @var NationalComponentStatisticApiResource */
    private $nationalComponentStatisticApiResource;

    /** @var UserTestQualityAction */
    private $userTestQualityAction;

    /** @var ViewVtsTestQualityAssertion */
    private $assertion;

    /** @var Url */
    private $urlPluginMock;

    /** @var AuthorisationServiceMock */
    private $authorisationServiceMock;

    /** @var SiteMapper */
    private $siteMapper;

    /** @var VehicleTestingStationDto */
    private $siteDto;
    /** @var NationalPerformanceApiResource */
    private $nationalPerformanceApiResourceMock;
    /** @var SiteComponentFailRateApiResource */
    private $siteComponentFailRateApiResource;
    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    protected function setUp()
    {
        $this->componentFailRateApiResource = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResource->expects($this->any())
            ->method('getForTesterAtSite')
            ->willReturn($this->buildUserPerformanceDto());

        $this->nationalComponentStatisticApiResource = XMock::of(NationalComponentStatisticApiResource::class);
        $this->nationalComponentStatisticApiResource->expects($this->any())
            ->method('getForDate')
            ->willReturn($this->buildNationalComponentStatisticsDto());

        $this->nationalPerformanceApiResourceMock = XMock::of(NationalPerformanceApiResource::class);
        $this->nationalPerformanceApiResourceMock->expects($this->any())
            ->method('getForMonths')
            ->willReturn($this->buildNationalStatisticsPerformanceDto());

        $this->siteComponentFailRateApiResource = XMock::of(SiteComponentFailRateApiResource::class);
        $this->siteComponentFailRateApiResource->expects($this->any())
            ->method('get')
            ->willReturn((new SiteComponentStatisticsDto())->setComponents([]));

        $this->authorisationServiceMock = new AuthorisationServiceMock();
        $this->authorisationServiceMock->grantedAtSite(self::REQUIRED_PERMISSION, self::SITE_ID);

        $this->assertion = new ViewVtsTestQualityAssertion($this->authorisationServiceMock);

        $this->urlPluginMock = XMock::of(Url::class);
        $this->urlPluginMock->method('fromRoute')
            ->willReturn(self::RETURN_LINK);

        $this->setUpSiteWithUser();

        $identity = (new Identity())->setUserId(self::USER_ID);
        $this->identityProvider = new FrontendIdentityProviderStub();
        $this->identityProvider->setIdentity($identity);

        $this->featureTogglesMock = XMock::of(FeatureToggles::class);
        $this->featureTogglesMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $this->userTestQualityAction = new UserTestQualityAction(
            $this->componentFailRateApiResource,
            $this->nationalComponentStatisticApiResource,
            $this->nationalPerformanceApiResourceMock,
            $this->siteComponentFailRateApiResource,
            $this->assertion,
            $this->siteMapper,
            XMock::of(ContextProvider::class),
            XMock::of(RouteMatch::class),
            $this->identityProvider,
            new TestDateTimeHolder(new \DateTime('2015-02-14')),
            $this->featureTogglesMock
        );
    }

    public function testAssertionIsChecked()
    {
        $this->authorisationServiceMock->clearAll();
        $this->setExpectedException(UnauthorisedException::class);

        $this->userTestQualityAction->execute(self::SITE_ID, 2, self::THREE_MONTHS_RANGE,
            VehicleClassGroupCode::BIKES, $this->breadcrumbs, self::IS_RETURN_TO_AE_TQI, $this->urlPluginMock);
    }

    public function testValuesReturnedWhenThereIsNoDataButTesterLinkedToSite()
    {
        $this->setUpServiceWithEmptyApiResponse();
        $this->setUpSiteWithUser();

        $result = $this->userTestQualityAction->execute(self::SITE_ID, self::USER_ID, self::THREE_MONTHS_RANGE,
            VehicleClassGroupCode::CARS_ETC, $this->breadcrumbs, self::IS_RETURN_TO_AE_TQI, $this->urlPluginMock);

        /** @var UserTestQualityViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertFalse($vm->getTable()->getAverageGroupStatisticsHeader()->hasTests());
    }

    private function setUpServiceWithEmptyApiResponse()
    {
        $this->componentFailRateApiResource = XMock::of(ComponentFailRateApiResource::class);
        $this->componentFailRateApiResource->method('getForTesterAtSite')
            ->willReturn($this->buildEmptyGroupPerformance());

        $this->userTestQualityAction = new UserTestQualityAction(
            $this->componentFailRateApiResource,
            $this->nationalComponentStatisticApiResource,
            $this->nationalPerformanceApiResourceMock,
            $this->siteComponentFailRateApiResource,
            $this->assertion,
            $this->siteMapper,
            XMock::of(ContextProvider::class),
            XMock::of(RouteMatch::class),
            $this->identityProvider,
            new TestDateTimeHolder(new \DateTime('2015-02-14')),
            $this->featureTogglesMock
        );
    }

    public function testValuesArePopulatedToLayoutResult()
    {
        $result = $this->userTestQualityAction->execute(self::SITE_ID, self::USER_ID, self::THREE_MONTHS_RANGE,
            VehicleClassGroupCode::BIKES, $this->breadcrumbs, self::IS_RETURN_TO_AE_TQI, $this->urlPluginMock);

        /** @var UserTestQualityViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertNotNull($vm);
        $this->assertNotNull($result->getTemplate());

        $this->assertNotNull($result->layout()->getPageTitle());
        $this->assertNotNull($result->layout()->getPageSubTitle());
        $this->assertNotNull($result->layout()->getTemplate());

        $this->assertSame(
            $this->breadcrumbs + [self::DISPLAY_NAME => null],
            $result->layout()->getBreadcrumbs()
        );
    }

    public function buildUserPerformanceDto()
    {
        $group = new MotTestingPerformanceDto();
        $group->setAverageTime(new TimeSpan(0, 0, 1, 1))
            ->setPercentageFailed(10.123)
            ->setTotal(5);

        $componentOne = new ComponentDto();
        $componentOne->setId(self::COMPONENT_ONE_ID)
            ->setName('Component ONE')
            ->setPercentageFailed(20.12);

        $componentTwo = new ComponentDto();
        $componentTwo->setId(self::COMPONENT_TWO_ID)
            ->setName('Second COMPONENT')
            ->setPercentageFailed(40.1234);

        $breakdown = new ComponentBreakdownDto();
        $breakdown->setGroupPerformance($group)
            ->setComponents([$componentOne, $componentTwo])
            ->setUserName(self::USERNAME)
            ->setDisplayName(self::DISPLAY_NAME);

        return $breakdown;
    }

    public function buildNationalComponentStatisticsDto()
    {
        $national = new NationalComponentStatisticsDto();
        $national->setMonthRange(self::THREE_MONTHS_RANGE);

        $brakes = new ComponentDto();
        $brakes->setId(self::COMPONENT_ONE_ID);
        $brakes->setPercentageFailed(50.123123);
        $brakes->setName('Brakes');

        $tyres = new ComponentDto();
        $tyres->setId(self::COMPONENT_TWO_ID);
        $tyres->setPercentageFailed(30.5523);
        $tyres->setName('Tyres');

        $userEmpty = new ComponentDto();
        $userEmpty->setId(self::COMPONENT_USER_EMPTY_ID);
        $userEmpty->setPercentageFailed(11.12312);
        $userEmpty->setName('Component that is missing in user stats');

        $national->setComponents([$brakes, $tyres, $userEmpty]);

        return $national;
    }

    public function buildNationalStatisticsPerformanceDto()
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

        $national->getReportStatus()->setIsCompleted(true);

        return $national;
    }

    public function buildEmptyGroupPerformance()
    {
        $group = new MotTestingPerformanceDto();
        $breakdown = new ComponentBreakdownDto();
        $breakdown->setGroupPerformance($group)->setComponents([]);

        return $breakdown;
    }

    private function setUpSiteWithUser() {
        $this->siteDto = (new VehicleTestingStationDto())
            ->setTestClasses([])
            ->setName(self::SITE_NAME)
            ->setPositions([
                (new RolesMapDto())
                    ->setPerson((new PersonDto())->setId(self::USER_ID))
            ]);

        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->siteMapper->expects(
            $this->any())
            ->method('getById')
            ->willReturn($this->siteDto);
    }

    public function buildUserBreakdownResponse($siteId, $userId, $groupCode, $monthRange) {
        if($monthRange === self::ONE_MONTH_RANGE) {
            return $this->buildEmptyGroupPerformance();
        } else {
            return $this->buildUserPerformanceDto();
        }
    }

    private function setUpSiteNoUser() {
        $this->siteDto = (new VehicleTestingStationDto())
            ->setTestClasses([])
            ->setName(self::SITE_NAME);

        $this->siteMapper = XMock::of(SiteMapper::class);
        $this->siteMapper->expects(
            $this->any())
            ->method('getById')
            ->willReturn($this->siteDto);
    }
}
