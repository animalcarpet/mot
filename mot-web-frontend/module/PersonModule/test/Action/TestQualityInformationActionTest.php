<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Action;


use Core\Action\ActionResult;
use Dvsa\Mot\Frontend\PersonModule\Action\TestQualityAction;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Mvc\Controller\Plugin\Url;

class TestQualityInformationActionTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $testerPerformanceDto = self::buildTesterPerformanceDto();
        $nationalPerformanceDto = self::buildNationalStatisticsPerformanceDto();

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

        $this->testQualityAction = new TestQualityAction(
            $this->testerPerformanceApiResourceMock,
            $this->nationalPerformanceApiResourceMock,
            $this->contextProviderMock,
            $this->testerGroupAuthorisationMapperMock,
            $this->viewTesterTestQualityAssertionMock,
            $this->personProfileRoutesMock
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

        $result = $this->testQualityAction->execute('1', '07', '2013', $this->url, [], $breadcrumb);

        $this->assertInstanceOf(ActionResult::class, $result);

        /** @var TestQualityInformationViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(TestQualityInformationViewModel::class, $vm);
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
    public function testProfileContext($context, $contextualProfileName, $componentContextualProfile)
    {
        /** @var ContextProvider|MockObj $contextProviderMock */
        $contextProviderMock = XMock::of(ContextProvider::class);
        $contextProviderMock->expects(
            $this->any())
            ->method('getContext')
            ->willReturn($context);
        $returnLinkText = 'Return to ' . strtolower($contextualProfileName);

        $testQualityAction = new TestQualityAction(
            $this->testerPerformanceApiResourceMock,
            $this->nationalPerformanceApiResourceMock,
            $contextProviderMock,
            $this->testerGroupAuthorisationMapperMock,
            $this->viewTesterTestQualityAssertionMock,
            $this->personProfileRoutesMock
        );
        $result = $testQualityAction->execute('1', '07', '2013', $this->url, [], []);

        $this->assertEquals($result->layout()->getPageSubTitle(), $contextualProfileName);

        /** @var TestQualityInformationViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertEquals($vm->getReturnLinkText(), $returnLinkText);
        $this->assertEquals($vm->getA()->getComponentLinkText(), sprintf($componentContextualProfile, 'A'));
        $this->assertEquals($vm->getB()->getComponentLinkText(), sprintf($componentContextualProfile, 'B'));

    }

    public function dataProviderContext()
    {
        return [
            [
                'context' => ContextProvider::YOUR_PROFILE_CONTEXT,
                'contextualProfileName' => 'Your profile',
                'componentContextualProfile' => 'View your Group %s failures by category in July 2013'
            ],
            [
                'context' => ContextProvider::USER_SEARCH_CONTEXT,
                'contextualProfileName' => 'User profile',
                'componentContextualProfile' => 'View Group %s failures by category in July 2013'
            ],
            [
                'context' => ContextProvider::AE_CONTEXT,
                'contextualProfileName' => 'User profile',
                'componentContextualProfile' => 'View Group %s failures by category in July 2013'
            ],
            [
                'context' => ContextProvider::VTS_CONTEXT,
                'contextualProfileName' => 'User profile',
                'componentContextualProfile' => 'View Group %s failures by category in July 2013'
            ],
        ];
    }

    public static function buildTesterPerformanceDto()
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

    public static function buildNationalStatisticsPerformanceDto()
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
}