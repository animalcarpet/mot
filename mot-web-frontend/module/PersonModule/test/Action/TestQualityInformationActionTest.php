<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Action;


use Core\Action\ViewActionResult;
use Dvsa\Mot\Frontend\PersonModule\Action\TestQualityAction;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterMultiSitePerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommon\PHPUnit\AbstractMotUnitTest;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Mvc\Controller\Plugin\Url;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiBreadcrumbs;

class TestQualityInformationActionTest extends AbstractMotUnitTest
{
    /** @var TestQualityAction */
    private $testQualityAction;

    /** @var TesterPerformanceApiResource $testerPerformanceApiResourceMock */
    private $testerPerformanceApiResourceMock;

    /** @var NationalPerformanceApiResource $nationalPerformanceApiResourceMock */
    private $nationalPerformanceApiResourceMock;

    /** @var ContextProvider $contextProviderMock */
    private $contextProviderMock;

    /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapperMock */
    private $testerGroupAuthorisationMapperMock;

    /** @var ViewTesterTestQualityAssertion $viewTesterTestQualityAssertionMock */
    private $viewTesterTestQualityAssertionMock;

    /** @var PersonProfileRoutes $personProfileRoutesMock  */
    private $personProfileRoutesMock;

    private $url;

    /** @var MockObj | TesterMultiSitePerformanceApiResource */
    private $multiSiteApiResource;

    private $testerTqiBreadcrumbs;

    public function setUp()
    {
        $testerPerformanceDto = $this->buildTesterPerformanceDto();
        $nationalPerformanceDto = $this->buildNationalStatisticsPerformanceDto();

        $this->testerPerformanceApiResourceMock = XMock::of(TesterPerformanceApiResource::class);
        $this->testerPerformanceApiResourceMock->method('get')
            ->willReturn($testerPerformanceDto);

        $this->nationalPerformanceApiResourceMock = XMock::of(NationalPerformanceApiResource::class);
        $this->nationalPerformanceApiResourceMock->method('getForDate')
            ->willReturn($nationalPerformanceDto);

        $this->contextProviderMock = XMock::of(ContextProvider::class);
        $this->contextProviderMock->expects(
            $this->any())
            ->method('getContext')
            ->willReturn(ContextProvider::YOUR_PROFILE_CONTEXT);

        $this->testerGroupAuthorisationMapperMock = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->testerGroupAuthorisationMapperMock->expects(
            $this->any())
            ->method('getAuthorisation')
            ->willReturn(self::buildTesterAuthorisation());

        $this->viewTesterTestQualityAssertionMock = XMock::of(ViewTesterTestQualityAssertion::class);
        $this->personProfileRoutesMock = XMock::of(PersonProfileRoutes::class);

        $this->multiSiteApiResource = XMock::of(TesterMultiSitePerformanceApiResource::class);

        $this->testerTqiBreadcrumbs = XMock::of(TesterTqiBreadcrumbs::class);
        $this
            ->testerTqiBreadcrumbs
            ->expects($this->any())
            ->method("getBreadcrumbs")
            ->willReturn(['breadcrumbLinkText' => 'http://breadcrumbsLink']);

        $this->testQualityAction = new TestQualityAction(
            $this->testerPerformanceApiResourceMock,
            $this->nationalPerformanceApiResourceMock,
            $this->contextProviderMock,
            $this->testerGroupAuthorisationMapperMock,
            $this->viewTesterTestQualityAssertionMock,
            $this->personProfileRoutesMock,
            $this->multiSiteApiResource,
            $this->testerTqiBreadcrumbs
        );

        $url = XMock::of(Url::class);
        $url
            ->expects($this->any())
            ->method("__invoke")
            ->willReturn('http://link');

        $this->url = $url;
    }


    public static function buildTesterAuthorisation()
    {
            $groupA = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, '');
            $groupB = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, '');

        $testerAuthorisation = new TesterAuthorisation(
            $groupA,
            $groupB
        );

        return $testerAuthorisation;
    }

    public function testActionResult()
    {
        $breadcrumb = ['breadcrumbLinkText' => 'http://breadcrumbsLink'];

        $this->mockMultiSiteData();

        /** @var ViewActionResult $result */
        $result = $this->testQualityAction->execute('1', '07', '2013', $this->url, []);

        $this->assertInstanceOf(ViewActionResult::class, $result);

        /** @var TestQualityInformationViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(TestQualityInformationViewModel::class, $vm);

        // sites from API are mapped to view model
        $this->assertCount(1, $vm->getA()->getSiteTests());
        $this->assertCount(3, $vm->getB()->getSiteTests());

        // sites are ordered by most tests done
        // Need4Meat did most tests in group B
        $this->assertEquals("Need4Meat", $vm->getB()->getSiteTests()[0]->getSiteName(), "Wrong order of sites");
        // Need4Meat did second mosts test in group B
        $this->assertEquals("Turbo Bourbon", $vm->getB()->getSiteTests()[1]->getSiteName(), "Wrong order of sites");
        // Need4Meat did least tests in group B
        $this->assertEquals("Wild Cat", $vm->getB()->getSiteTests()[2]->getSiteName(), "Wrong order of sites");

        $site1Vm = $vm->getA()->getSiteTests()[0];
        $site2Vm = $vm->getB()->getSiteTests()[0];
        $site3Vm = $vm->getB()->getSiteTests()[1];
        $site4Vm = $vm->getB()->getSiteTests()[2];

        // check if site names are copied to view model
        $this->assertEquals("Fancy Cars", $site1Vm->getSiteName());
        $this->assertEquals("Need4Meat", $site2Vm->getSiteName());
        $this->assertEquals("Turbo Bourbon", $vm->getB()->getSiteTests()[1]->getSiteName());
        $this->assertEquals("Wild Cat", $vm->getB()->getSiteTests()[2]->getSiteName());

        // address is translated
        $this->assertEquals("Unit 4, Mod Way Industrial Park, MD99 4RT", $site1Vm->getSiteAddress());
        $this->assertEquals("10 Offroad, Hottap, HT6 4RF", $site2Vm->getSiteAddress());

        // sites with no address are handled
        $this->assertEquals("", $site3Vm->getSiteAddress());

        // check if ids names are copied to view model
        $this->assertEquals(15, $site1Vm->getSiteId());
        $this->assertEquals(103, $site2Vm->getSiteId());

        // check if total tests done are copied to view model
        $this->assertEquals(11, $site1Vm->getTestsDone());
        $this->assertEquals(51, $site2Vm->getTestsDone());

        // check if average test time is copied to view
        $this->assertTimeSpanEquals(new TimeSpan(1, 3, 4, 6), $site1Vm->getAverageTestTime());
        $this->assertTimeSpanEquals(new TimeSpan(1, 3, 4, 55), $site2Vm->getAverageTestTime());

        // check if average vehicle age is copied to view model
        // handling rounding of vehicle with 0 age
        $this->assertSame("1", $site1Vm->getAverageVehicleAgeAsString());
        $this->assertSame("1", $site2Vm->getAverageVehicleAgeAsString());
        $this->assertSame("2", $site3Vm->getAverageVehicleAgeAsString());
        // handling lack of data about vehicles average age
        $this->assertSame("Not available", $site4Vm->getAverageVehicleAgeAsString());

        // check if average test time is copied to view
        $this->assertSame("14%", $site1Vm->getTestsFailedPercentage());
        $this->assertSame("0%", $site2Vm->getTestsFailedPercentage());
        $this->assertSame("16%", $site3Vm->getTestsFailedPercentage());
        $this->assertSame("100%", $site4Vm->getTestsFailedPercentage());

        $this->assertEquals($result->layout()->getBreadcrumbs(), $breadcrumb);
        $this->assertEquals($result->layout()->getPageLede(), 'Tests done at all associated sites in July 2013');
        $this->assertEquals($result->layout()->getPageTitle(), 'Test quality information');
    }

    /**
     * @dataProvider dataProviderContext
     * @param $context
     * @param $contextualProfileName
     * @param $componentContextualProfile
     */
    public function testProfileContext($context, $contextualProfileName, $componentContextualProfile, $componentContextualProfileGroup)
    {
        /** @var ContextProvider|MockObj $contextProviderMock */
        $contextProviderMock = XMock::of(ContextProvider::class);
        $contextProviderMock->expects(
            $this->any())
            ->method('getContext')
            ->willReturn($context);
        $returnLinkText = 'Return to ' . strtolower($contextualProfileName);

        $this->mockMultiSiteData();

        $testQualityAction = new TestQualityAction(
            $this->testerPerformanceApiResourceMock,
            $this->nationalPerformanceApiResourceMock,
            $contextProviderMock,
            $this->testerGroupAuthorisationMapperMock,
            $this->viewTesterTestQualityAssertionMock,
            $this->personProfileRoutesMock,
            $this->multiSiteApiResource,
            $this->testerTqiBreadcrumbs
        );
        $result = $testQualityAction->execute('1', '07', '2013', $this->url, [], []);

        $this->assertEquals($result->layout()->getPageSubTitle(), $contextualProfileName);

        /** @var TestQualityInformationViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertEquals($vm->getReturnLinkText(), $returnLinkText);
        $this->assertEquals($vm->getA()->getComponentLinkText(), $componentContextualProfile);
        $this->assertEquals($vm->getA()->getComponentLinkTextGroup(), sprintf($componentContextualProfileGroup, 'A'));
        $this->assertEquals($vm->getB()->getComponentLinkText(), $componentContextualProfile);
        $this->assertEquals($vm->getB()->getComponentLinkTextGroup(), sprintf($componentContextualProfileGroup, 'B'));

    }

    public function dataProviderContext()
    {
        return [
            [
                'context' => ContextProvider::YOUR_PROFILE_CONTEXT,
                'contextualProfileName' => 'Your profile',
                'componentContextualProfile' => 'failures by category in July 2013',
                'componentContextualProfileGroup' => 'View your Group %s ',
            ],
            [
                'context' => ContextProvider::USER_SEARCH_CONTEXT,
                'contextualProfileName' => 'User profile',
                'componentContextualProfile' => 'failures by category in July 2013',
                'componentContextualProfileGroup' => 'View Group %s '
            ],
            [
                'context' => ContextProvider::AE_CONTEXT,
                'contextualProfileName' => 'User profile',
                'componentContextualProfile' => 'failures by category in July 2013',
                'componentContextualProfileGroup' => 'View Group %s ',
            ],
            [
                'context' => ContextProvider::VTS_CONTEXT,
                'contextualProfileName' => 'User profile',
                'componentContextualProfile' => 'failures by category in July 2013',
                'componentContextualProfileGroup' => 'View Group %s ',
            ],
        ];
    }

    private function buildTesterPerformanceDto()
    {
        $tester = new TesterPerformanceDto();

        $stats1 = new EmployeePerformanceDto();

        $stats1->setUsername("Tester");
        $stats1->setTotal(1);
        $stats1->setAverageTime(new TimeSpan(1, 1, 1, 1));
        $stats1->setPercentageFailed(100);

        $tester->setGroupAPerformance($stats1);

        $stats2 = new EmployeePerformanceDto();

        $stats2->setUsername("Tester");
        $stats2->setTotal(200);
        $stats2->setAverageTime(new TimeSpan(2, 2, 2, 2));
        $stats2->setPercentageFailed(33.33);

        $tester->setGroupBPerformance($stats2);

        return $tester;
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

        $national->getReportStatus()->setIsCompleted(true);

        return $national;
    }

    private function mockMultiSiteData()
    {
        $reportDto = new TesterMultiSitePerformanceReportDto();
        $groupASitesDto = [
            (new TesterMultiSitePerformanceDto())
                ->setSiteName("Fancy Cars")
                ->setSiteId(15)
                ->setSiteAddress((new AddressDto())
                ->setAddressLine1("Unit 4")
                ->setAddressLine2("Mod Way Industrial Park")
                ->setPostcode("MD99 4RT"))
                ->setTotal(11)
                ->setAverageTime(new TimeSpan(1, 3, 4, 6))
                ->setAverageVehicleAgeInMonths(1)
                ->setIsAverageVehicleAgeAvailable(true)
                ->setPercentageFailed(13.976)
        ];
        $groupBSitesDto = [
            (new TesterMultiSitePerformanceDto())
                ->setSiteName("Turbo Bourbon")
                ->setTotal(40)
                ->setAverageTime(new TimeSpan(1, 4 , 6, 59))
                ->setIsAverageVehicleAgeAvailable(true)
                ->setAverageVehicleAgeInMonths(18)
                ->setPercentageFailed(15.71),
            (new TesterMultiSitePerformanceDto())
                ->setTotal(19)
                ->setSiteName("Wild Cat")
                ->setAverageTime(new TimeSpan(1, 4 , 6, 59))
                ->setIsAverageVehicleAgeAvailable(false)
                ->setAverageVehicleAgeInMonths(0)
                ->setPercentageFailed(99.950001),
            (new TesterMultiSitePerformanceDto())
                ->setSiteName("Need4Meat")
                ->setSiteId(103)
                ->setSiteAddress((new AddressDto())
                    ->setAddressLine1("10 Offroad")
                    ->setTown("Hottap")
                    ->setPostcode("HT6 4RF"))
                ->setTotal(51)
                ->setAverageTime(new TimeSpan(1, 3, 4, 55))
                ->setIsAverageVehicleAgeAvailable(true)
                ->setAverageVehicleAgeInMonths(17)
                ->setPercentageFailed(0.049999),
        ];
        $reportDto->setA($groupASitesDto);
        $reportDto->setB($groupBSitesDto);

        $this->multiSiteApiResource->expects($this->any())
            ->method('get')
            ->willReturn($reportDto);
    }
}