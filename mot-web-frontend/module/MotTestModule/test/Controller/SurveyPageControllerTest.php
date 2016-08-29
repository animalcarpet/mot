<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use Application\Service\LoggedInUserManager;
use Core\Service\MotEventManager;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SurveyPageController;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Zend\EventManager\EventManager;
use Zend\Session\Container;

/**
 * Class SurveyPageControllerTest
 * @package Dvsa\Mot\Frontend\MotTestModuleTest\Controller
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

    protected function setUp()
    {
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

        $this->setController(
            new SurveyPageController(
                $this->surveyService
            )
        );
        $this->getController()->setServiceLocator($this->serviceManager);

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
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => true]);

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
     * @param bool   $featureToggleValue
     * @param bool   $shouldRedirect
     */
    public function testRedirectToThanksPage($token, $satisfactionRating, $featureToggleValue, $shouldRedirect)
    {
        $this->setParams(['token' => $token]);
        $this->setPostAndPostParams(
            [
                SurveyPageController::SATISFACTION_RATING => $satisfactionRating,
                SurveyPageController::TOKEN_KEY => $token
            ]
        );

        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => $featureToggleValue]);

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
            ['survey', 1, true, true],
            [null, 2, true, false],
            ['testToken', 20, true, true],
            ['testToken', 3, false, false]
        ];
    }

    /**
     * @dataProvider testThanksActionDataProvider
     * @group survey_page_controller_tests
     *
     * @param $referer
     * @param $featureToggleValue
     * @param $expectedResponse
     */
    public function testThanksAction($referer, $featureToggleValue, $expectedResponse)
    {
        $_SERVER['HTTP_REFERER'] = $referer;
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => $featureToggleValue]);

        $this->getResponseForAction('thanks');

        $this->assertResponseStatus($expectedResponse);
    }

    public function testThanksActionDataProvider()
    {
        return [
            ['survey', true, self::HTTP_OK_CODE],
            ['survey', false, self::HTTP_ERR_404],
            ['asnmfp', false, self::HTTP_ERR_404],
            ['asddsd', true, self::HTTP_ERR_404],
        ];
    }

    /**
     * @dataProvider testDownloadReportCsvActionDataProvider
     * @group survey_page_controller_tests
     *
     * @param $featureToggleValue
     */
    public function testCanDownloadReportCsvActionWithPermission($featureToggleValue)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT]);

        $this->setParams(['month' => '2016-04']);
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => $featureToggleValue]);

        $this->getResponseForAction('downloadReportCsv');

        if ($featureToggleValue) {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        } else {
            $this->assertResponseStatus(self::HTTP_ERR_404);
        }
    }

    /**
     * @dataProvider testDownloadReportCsvActionDataProvider
     * @group survey_page_controller_tests
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     *
     * @param $featureToggleValue
     */
    public function testCannotDownloadReportCsvActionWithoutPermission($featureToggleValue)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());
        $this->setupAuthorizationService([]);

        $this->setParams(['month' => '2016-04']);
        $this->withFeatureToggles([FeatureToggle::SURVEY_PAGE => $featureToggleValue]);

        $this->getResponseForAction('downloadReportCsv');

        $this->assertResponseStatus(self::HTTP_ERR_404);
    }

    /**
     * @return array
     */
    public function testDownloadReportCsvActionDataProvider()
    {
        return [
            [true],
            [false],
        ];
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

        return $surveyService;
    }
}
