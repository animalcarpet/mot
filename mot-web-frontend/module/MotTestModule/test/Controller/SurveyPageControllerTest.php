<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use Application\Service\LoggedInUserManager;
use Core\Service\MotEventManager;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DateTimeImmutable;
use Dvsa\Mot\Frontend\GoogleAnalyticsModule\ControllerPlugin\DataLayerPlugin;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SurveyPageController;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Survey\DownloadableSurveyReport;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Survey\DownloadableSurveyReports;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Zend\EventManager\EventManager;
use Zend\Session\Container;

/**
 * Class SurveyPageControllerTest.
 */
class SurveyPageControllerTest extends AbstractFrontendControllerTestCase
{
    private $loggedInUserManagerMock;

    /**
     * @var SurveyService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $surveyService;

    /**
     * @var MotEventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var Container|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionManager;

    /**
     * @var DataLayerPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataLayerPlugin;

    /**
     * @var DateTimeImmutable
     */
    private $datetime;

    protected function setUp()
    {
        $this->datetime = new DateTimeImmutable();

        Bootstrap::setupServiceManager();
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->surveyService = $this->createSurveyService();
        $this->eventManager = $this->getMockBuilder(MotEventManager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionManager = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $logger = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setController(new SurveyPageController($this->surveyService, $logger));

        $this->dataLayerPlugin = $this->getMockBuilder(DataLayerPlugin::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->getController()->setServiceLocator($this->serviceManager);
        $this->getController()->getPluginManager()->setService('gtmDataLayer', $this->dataLayerPlugin);

        $this->loggedInUserManagerMock = XMock::of(
            LoggedInUserManager::class,
            []
        );

        $this->serviceManager->setService('LoggedInUserManager', $this->loggedInUserManagerMock);
        parent::setUp();
    }

    /**
     * @group survey_page_controller_tests
     */
    public function testCanAccessReportPageWithCorrectPermission()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT]);

        $this->getResponseForAction('reports');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @group survey_page_controller_tests
     *
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testCannotAccessReportPageWithoutCorrectPermission()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([]);

        $this->getResponseForAction('reports');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @dataProvider testRedirectToThanksPageDataProvider
     * @group survey_page_controller_tests
     *
     * @param string $token
     * @param int    $satisfactionRating
     * @param bool   $shouldRedirect
     */
    public function testRedirectToThanksPage($token, $satisfactionRating, $shouldRedirect)
    {
        $this->setParams(['token' => $token]);
        $this->setPostAndPostParams(
            [
                SurveyPageController::SATISFACTION_RATING => $satisfactionRating,
                SurveyPageController::TOKEN_KEY => $token,
            ]
        );

        $this->getResponseForAction('index');

        if ($shouldRedirect) {
            $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
        } else {
            $this->assertResponseStatus(self::HTTP_ERR_404);
        }
    }

    /**
     * @group survey_page_controller_tests
     *
     * @return array
     */
    public function testRedirectToThanksPageDataProvider()
    {
        return [
            ['survey', 1, true],
            [null, 2, false],
            ['testToken', 20, true],
        ];
    }

    public function testRedirectToLoginWithNoToken()
    {
        $this->getResponseForAction('index', ['token' => null]);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testRedirectToLoginWithInvalidToken()
    {
        $this->surveyService->method('isTokenValid')->willReturn(false);
        $this->getResponseForAction('index', ['token' => 'invalid']);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function test200WithNoPostAndValidToken()
    {
        $this->surveyService->method('isTokenValid')->willReturn(true);
        $this->getResponseForAction('index', ['token' => 'valid']);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testGetReportsWithData()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT]);

        ob_start();
        $this->getResponseForAction('downloadReportCsv', [
            'year' => $this->datetime->format('Y'), 'month' => $this->datetime->format('m'),
        ]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
        ob_end_clean();
    }

    public function testGetReportsWithNoData()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT]);

        ob_start();
        $this->getResponseForAction('downloadReportCsv', [
            'year' => $this->datetime->format('Y'), 'month' => $this->datetime->format('m'),
        ]);
        ob_end_clean();

        $this->getResponseForAction('downloadReportCsv', ['month' => 'May']);
        $this->assertResponseStatus(302);
    }

    /**
     * @dataProvider testThanksActionDataProvider
     * @group survey_page_controller_tests
     *
     * @param $referer
     * @param $expectedResponse
     */
    public function testThanksAction($referer, $expectedResponse)
    {
        $_SERVER['HTTP_REFERER'] = $referer;

        $this->getResponseForAction('thanks');

        $this->assertResponseStatus($expectedResponse);
    }

    public function testThanksActionDataProvider()
    {
        return [
            ['survey', self::HTTP_OK_CODE],
            ['asnmfp', self::HTTP_ERR_404],
        ];
    }

    /**
     * @group survey_page_controller_tests
     */
    public function testCanDownloadReportCsvActionWithPermission()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT]);

        $this->setParams(['year' => $this->datetime->format('Y'), 'month' => $this->datetime->format('m')]);

        ob_start();
        $this->getResponseForAction('downloadReportCsv');
        ob_end_clean();

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @group survey_page_controller_tests
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testCannotDownloadReportCsvActionWithoutPermission()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([]);

        $this->setParams(['month' => '2016-04']);

        $this->getResponseForAction('downloadReportCsv');

        $this->assertResponseStatus(self::HTTP_ERR_404);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createSurveyService()
    {
        $surveyService = $this
            ->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reports = [
            $this->datetime->format('Y') => [
                $this->datetime->format('m') => (new DownloadableSurveyReport($this->datetime, 666, 'some,csv,data')),
            ],
        ];

        $surveyReports = new DownloadableSurveyReports($reports);

        $surveyService
            ->expects($this->any())
            ->method('getSurveyReports')
            ->willReturn($surveyReports);

        return $surveyService;
    }
}
