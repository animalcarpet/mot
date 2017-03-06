<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTestTest\Controller;

use Application\Helper\PrgHelper;
use Core\Service\MotEventManager;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\GoogleAnalyticsModule\ControllerPlugin\DataLayerPlugin;
use Dvsa\Mot\Frontend\GoogleAnalyticsModule\TagManager\DataLayer;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\ApiClient\MotTest\DuplicateCertificate\MotTestDuplicateCertificateApiResource;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\OtpApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTestTest\TestHelper\Fixture;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Mvc\Controller\Plugin\Forward;
use Zend\Session\Container;

/**
 * Class MotTestControllerTest.
 */
class MotTestControllerTest extends AbstractFrontendControllerTestCase
{
    /* @var MotFrontendAuthorisationServiceInterface|MockObj $authServiceMock */
    private $authServiceMock;

    /** @var SurveyService|MockObj $surveyServiceMock */
    private $surveyServiceMock;

    /** @var MotEventManager|MockObj $eventManagerMock*/
    private $motEventManagerMock;

    /** @var MotTestDuplicateCertificateApiResource|MockObj $motTestDuplicateCertificateApiResourceMock*/
    private $motTestDuplicateCertificateApiResourceMock;
    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->authServiceMock = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->surveyServiceMock = $this->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->motEventManagerMock = $this->getMockBuilder(MotEventManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['trigger'])
            ->getMock();

        $odometerViewObject = XMock::of(OdometerReadingViewObject::class);

        $this->motTestDuplicateCertificateApiResourceMock = $this->getMockBuilder(MotTestDuplicateCertificateApiResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEditAllowed'])
            ->getMock();

        $this->controller = new MotTestController(
            $this->authServiceMock,
            $this->motEventManagerMock,
            $odometerViewObject,
            $this->motTestDuplicateCertificateApiResourceMock
        );

        $dataLayerPlugin = new DataLayerPlugin(new DataLayer());
        $this->getController()->getPluginManager()->setService('gtmDataLayer', $dataLayerPlugin);

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );
        $this->controller->setServiceLocator($serviceManager);

        parent::setUp();

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
    }

    private function getMockMotTestServiceClient()
    {
        if ($this->mockMotTestServiceClient == null) {
            $this->mockMotTestServiceClient = XMock::of(MotTestService::class);
        }
        return $this->mockMotTestServiceClient;
    }

    private function getMockVehicleServiceClient()
    {
        if ($this->mockVehicleServiceClient == null) {
            $this->mockVehicleServiceClient = XMock::of(VehicleService::class);
        }
        return $this->mockVehicleServiceClient;
    }

    public function testRequestIsForwardedToMotTestResultsControllerIfFeatureToggleIsEnabled()
    {
        $this->withFeatureToggles([FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS => true]);

        $forwardPlugin = $this
            ->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $forwardPlugin
            ->expects($this->once())
            ->method('dispatch');

        $this->getController()->getPluginManager()->setService('forward', $forwardPlugin);

        $this->getResultForAction('index', ['motTestNumber' => 656402615654]);
    }

    public function testMotTestIndexCanBeAccessedForAuthenticatedRequest()
    {
        $motTestNr = 1;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $result = $this->getResultForAction('index', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
        $this->assertEquals($testMotTestData, $result->motTest);
    }

    public function testMotTestIndexWithoutIdParameterFails()
    {
        $this->controller->dispatch($this->request);

        $this->assertResponseStatus(self::HTTP_ERR_404);
    }

    public function testMotTestUpdateOdometerPostWithValidData()
    {
        $motTestNumber = 1;
        $odometerValue = '100';
        $odometerUnit = OdometerUnit::MILES;
        $odometerResultType = OdometerReadingResultType::OK;

        $expectedRestPostData = [
            'value' => $odometerValue,
            'unit' => $odometerUnit,
            'resultType' => $odometerResultType,
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('put')
            ->with(MotTestUrlBuilder::odometerReading($motTestNumber)->toString(), $expectedRestPostData);

        //  --  request & check    --
        $postParams = [
            'odometer' => $odometerValue,
            'unit' => $odometerUnit,
            'resultType' => $odometerResultType,
        ];

        $this->getResultForAction2('post', 'updateOdometer', ['motTestNumber' => $motTestNumber], null, $postParams);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::motTest($motTestNumber));
    }

    public function testUpdateOdometerDisplaysErrorMessageAndRedirectsWithInvalidData()
    {
        $motTestNumber = 1;
        $odometerValue = '';
        $odometerUnit = OdometerUnit::MILES;
        $odometerResultType = OdometerReadingResultType::OK;

        $this->getFlashMessengerMockForAddErrorMessage('Odometer value must be entered to update odometer reading');

        //  --  request & check    --
        $postParams = [
            'odometer' => $odometerValue,
            'unit' => $odometerUnit,
            'resultType' => $odometerResultType,
        ];

        $this->getResultForAction2('post', 'updateOdometer', ['motTestNumber' => $motTestNumber], null, $postParams);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::motTest($motTestNumber));
    }

    public function testMotTestUpdateOdometerPostWithValidDataNoOdometerValue()
    {
        $motTestNumber = 1;
        $odometerValue = '100';
        $odometerUnit = OdometerUnit::MILES;
        $odometerResultType = OdometerReadingResultType::NO_ODOMETER;

        $expectedRestPostData = [
            'resultType' => $odometerResultType,
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('put')
            ->with(MotTestUrlBuilder::odometerReading($motTestNumber)->toString(), $expectedRestPostData);

        //  --  request & check    --
        $postParams = [
            'odometer' => $odometerValue,
            'unit' => $odometerUnit,
            'resultType' => $odometerResultType,
        ];

        $this->getResultForAction2('post', 'updateOdometer', ['motTestNumber' => $motTestNumber], null, $postParams);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::motTest($motTestNumber));
    }

    public function testMotTestIndexPostWithInvalidOdometerLetters()
    {
        $this->getFlashMessengerMockForAddErrorMessage(MotTestController::ODOMETER_FORM_ERROR_MESSAGE);

        $postParams = [
            'odometer' => 'ABCD',
            'unit' => 'km',
            'resultType' => OdometerReadingResultType::OK,
        ];
        $this->getResultForAction2('post', 'updateOdometer', null, null, $postParams);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testMotTestIndexPostWithInvalidOdometerWithDecimalPoint()
    {
        $this->getFlashMessengerMockForAddErrorMessage(MotTestController::ODOMETER_FORM_ERROR_MESSAGE);

        $postParams = [
            'odometer' => '12.44',
            'unit' => 'km',
            'resultType' => OdometerReadingResultType::OK,
        ];
        $this->getResultForAction2('post', 'updateOdometer', null, null, $postParams);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testMotTestIndexPostWithInvalidOdometerWithMoreThanSixDigits()
    {
        $this->getFlashMessengerMockForAddErrorMessage(MotTestController::ODOMETER_FORM_ERROR_MESSAGE);

        $postParams = [
            'odometer' => '1234567',
            'unit' => 'km',
            'resultType' => OdometerReadingResultType::OK,
        ];
        $this->getResultForAction2('post', 'updateOdometer', null, null, $postParams);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testMotTestIndexPostWithInvalidOdometerWithComma()
    {
        $this->getFlashMessengerMockForAddErrorMessage(MotTestController::ODOMETER_FORM_ERROR_MESSAGE);

        $postParams = [
            'odometer' => '12,44',
            'unit' => 'km',
            'resultType' => OdometerReadingResultType::OK,
        ];
        $this->getResultForAction2('post', 'updateOdometer', null, null, $postParams);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testMotTestIndexPostWithInvalidOdometerWithWhiteSpaceOnly()
    {
        $this->getFlashMessengerMockForAddErrorMessage(MotTestController::ODOMETER_VALUE_REQUIRED_MESSAGE);

        $postParams = [
            'odometer' => '  ',
            'unit' => 'km',
            'resultType' => OdometerReadingResultType::OK,
        ];
        $this->getResultForAction2('post', 'updateOdometer', null, null, $postParams);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testSubmitTestResultsWithStatus()
    {
        $authServiceMock = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('AuthorisationService', $authServiceMock);
        $authServiceMock->expects($this->once())
            ->method('isVehicleExaminer')
            ->willReturn(true);

        $motTestNumber = 9999;
        $status = 'testStatus';
        $siteid = 1;

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'siteid' => $siteid,
            'onePersonTest' => 1,
            'onePersonReInspection' => 1,
        ];

        $this->getResultForAction2(
            'post', 'displayTestSummary', ['motTestNumber' => $motTestNumber], null, $postParams
        );

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::showResult($motTestNumber));
    }

    public function testSubmitTestResultsCatchOtpApplicationException()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $motTestNumber = 1;
        $status = 'testStatus';
        $siteid = 1;

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'siteid' => $siteid,
            'onePersonTest' => 1,
            'onePersonReInspection' => 1,
            'clientIp' => '0.0.0.0',
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('post')
            ->with(MotTestUrlBuilder::motTestStatus($motTestNumber), $postParams)
            ->willThrowException(
                new OtpApplicationException(
                    '/',
                    'post',
                    [],
                    10,
                    [],
                    ['message' => 'error', 'shortMessage' => 'error']
                )
            );

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $this->getResultForAction2(
            'post', 'displayTestSummary', ['motTestNumber' => $motTestNumber], null, $postParams
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testSubmitTestResultsCatchRestApplicationException()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $motTestNumber = 1;
        $status = 'testStatus';
        $siteid = 1;

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'siteid' => $siteid,
            'onePersonTest' => 1,
            'onePersonReInspection' => 1,
            'clientIp' => '0.0.0.0',
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('post')
            ->with(MotTestUrlBuilder::motTestStatus($motTestNumber), $postParams)
            ->willThrowException(new RestApplicationException('/', 'post', [], 10, [['displayMessage' => 'error']]));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $this->getResultForAction2(
            'post', 'displayTestSummary', ['motTestNumber' => $motTestNumber], null, $postParams
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testSubmitTestResultsCatchRestApplicationExceptionRedirect()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $motTestNumber = 1;
        $status = 'testStatus';
        $siteid = 1;

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'siteid' => $siteid,
            'onePersonTest' => 1,
            'onePersonReInspection' => 1,
            'clientIp' => '0.0.0.0',
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('post')
            ->with(MotTestUrlBuilder::motTestStatus($motTestNumber), $postParams)
            ->willThrowException(
                new RestApplicationException(
                    '/',
                    'post',
                    [],
                    10,
                    [['displayMessage' => 'This test has been aborted by DVSA and cannot be continued']]
                )
            );

        $this->getResultForAction2(
            'post', 'displayTestSummary', ['motTestNumber' => $motTestNumber], null, $postParams
        );

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::showResult($motTestNumber));
    }

    public function testDisplayTestResultsWithStatus()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $motTestNumber = 1;
        $status = 'testStatus';
        $siteid = 1;

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'siteid' => $siteid,
            'onePersonTest' => 1,
            'onePersonReInspection' => 1,
            'clientIp' => '0.0.0.0',
        ];

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $this->getResultForAction2(
            'get', 'displayTestSummary', ['motTestNumber' => $motTestNumber], null, $postParams
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCancelMotTestWithValidPostDataOk()
    {
        $motTestNumber = 1;
        $status = 'CANCEL';
        $reasonForCancel = 1;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'reasonForCancel' => $reasonForCancel,
        ];

        $this->getResultForAction2('post', 'cancelMotTest', ['motTestNumber' => $motTestNumber], null, $postParams);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::cancelled($motTestNumber));
    }

    public function testAbandonMotTestWithValidPostDataOk()
    {
        $motTestNumber = 1;
        $status = 'CANCEL';
        $reasonForCancel = 1;
        $oneTimePassword = '123456';

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        //  --  request & check    --
        $postParams = [
            'status' => $status,
            'reasonForCancel' => $reasonForCancel,
            'oneTimePassword' => $oneTimePassword,
        ];

        $this->getResultForAction2('post', 'cancelMotTest', ['motTestNumber' => $motTestNumber], null, $postParams);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::cancelled($motTestNumber));
    }

    public function testAbandonMotTestWithWrongOneTimePassword()
    {
        $motTestNumber = 1;
        $status = 'CANCEL';
        $reasonForCancel = 1;
        $oneTimePassword = '123456';

        $expectedRestPostData = [
            'status' => $status,
            'reasonForCancelId' => $reasonForCancel,
            'oneTimePassword' => $oneTimePassword,
            'clientIp' => '0.0.0.0',
        ];

        $restClientMock = $this->getRestClientMockForServiceManager();

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $restResourcePath = MotTestUrlBuilder::motTestStatus($motTestNumber);
        $restClientMock->expects($this->once())
            ->method('post')
            ->with($restResourcePath, $expectedRestPostData)
            ->willThrowException(new OtpApplicationException($restResourcePath, null, null, 403, [], []));

        //  --  request & check --
        $postParams = [
            'status' => $status,
            'reasonForCancelId' => $reasonForCancel,
            'oneTimePassword' => $oneTimePassword,
        ];

        $this->getResultForAction2('post', 'cancelMotTest', ['motTestNumber' => $motTestNumber], null, $postParams);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @dataProvider cancelledMotTestActionDataProvider
     */
    public function testCancelledMotTestCanBeAccessedAuthenticatedRequest($motTestStatus, $expectedTestDocument)
    {
        $motTestNr = 1;

        $this->setupAuthorizationService();

        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);

        if ($motTestStatus === MotTestStatusName::ABORTED){
            $testMotTestData->status = MotTestStatusName::ABORTED;
        } else {
            $testMotTestData->status = MotTestStatusName::ABANDONED;
        }

        $motTest = new MotTest($testMotTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($motTest));

        $result = $this->getResultForAction('cancelledMotTest', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
        $this->assertEquals($expectedTestDocument, $result->testDocument);
    }

    public static function cancelledMotTestActionDataProvider()
    {
        return [
            [MotTestStatusName::ABORTED, MotTestController::TEST_DOCUMENT_VT32],
            [MotTestStatusName::ABANDONED, MotTestController::TEST_DOCUMENT_VT30],
        ];
    }

    public function testDisplayTestSummaryCanBeAccessedAuthenticatedRequest()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $motTestNr = 1;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $result = $this->getResultForAction('displayTestSummary', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testIndexActionWithMysteryShopperTestType()
    {
        $motTestNr = (int) rand(1e12, 1e13 - 1);
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;

        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $result = $this->getResultForAction('index', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testDisplayTestSummaryWithMysteryShopperTestType()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $motTestNr = (int) rand(1, 1000);
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;
        $motTestData->testTypeCode = MotTestTypeCode::MYSTERY_SHOPPER;

        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $result = $this->getResultForAction('displayTestSummary', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testDisplayCertificateSummary()
    {
        $motTestNr = (int) rand(1, 1000);

        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;
        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $this->setupAuthorizationService([PermissionInSystem::CERTIFICATE_READ]);

        $result = $this->getResultForAction('displayCertificateSummary', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
        $this->assertEquals($motTest, $result->motDetails);
    }

    /**
     * Post from summary page, mot test already finished (not active status), redirect to finish page by motTestNumber.
     */
    public function testDisplaySummaryPostActionGivenMotTestNotActiveShouldRedirectToFinish()
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $motTestNumber = 1;
        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $restClient = $this->getRestClientMockForServiceManager();
        $this->mockMethod(
            $restClient, 'get', null,
            ['data' => $testMotTestData],
            MotTestUrlBuilder::motTest($motTestNumber)->toString()
        );

        $this->getResultForAction2('post', 'displayTestSummary', ['motTestNumber' => $motTestNumber]);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::showResult($motTestNumber));
    }

    /**
     * Check for double post.
     *
     * @dataProvider dataProviderTestDoublePost
     */
    public function testDoublePost($action)
    {
        $this->setupAuthorizationService(
            [PermissionInSystem::MOT_TEST_CONFIRM, PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE]
        );

        $tokenGuid = 'testToken';

        $session = new Container('prgHelperSession');
        $session->offsetSet($tokenGuid, 'redirectUrl');

        $postParams = [
            PrgHelper::FORM_GUID_FIELD_NAME => $tokenGuid,
        ];
        $this->getResultForAction2('post', 'displayTestSummary', null, null, $postParams);

        $this->assertRedirectLocation2('redirectUrl');
    }

    public function dataProviderTestDoublePost()
    {
        return [
            ['action' => 'displayTestSummary'],
            ['action' => 'cancelMotTest'],
        ];
    }

    public function testDeleteRfrActionDeletesOk()
    {
        $motTestNumber = 1;
        $rfrId = 123;

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->once())
            ->method('delete')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $rfrId));

        //  --  request & check --
        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'rfr-id' => $rfrId,
        ];

        $this->getResultForAction2('delete', 'deleteReasonForRejection', $routeParams);

        $this->assertRedirectLocation2(MotTestUrlBuilderWeb::motTest($motTestNumber));
    }

    public function testPrintMotTestCanBeAccessedAuthenticatedRequest()
    {

        $this->setupAuthorizationService();

        $motTestNr = 1;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $this->motEventManagerMock->expects($this->once())
            ->method('trigger');

        $result = $this->getResultForAction('testResult', ['motTestNumber' => $motTestNr]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
        $this->assertEquals($testMotTestData, $result->motDetails);
    }

    public function retrievePdfDuplicateDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    public static function testIpExtractionFromHeaderDataProvider()
    {
        return [
            ['1.1.1.1', '1.1.1.1'],
            ['1.1.1.1, 2.2.2.2, 3.3.3.3', '1.1.1.1'],
            ['', '0.0.0.0'],
            [',', '0.0.0.0'],
        ];
    }

    /**
     * @dataProvider dataProviderForIsNonMotFlagOnTestSummaryResponse
     */
    public function testIsNonMotFlagOnTestSummaryResponse(
        $expectedNonMotFlag,
        $userHasNonMotTestPermission,
        $testHasNonMotTestType
    ) {
        $this->setUpNonMotDependencies($userHasNonMotTestPermission);

        $motTestNr = (int) rand(1, 1000);
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;

        if ($testHasNonMotTestType) {
            $motTestData->testTypeCode = MotTestTypeCode::NON_MOT_TEST;
        }

        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $siteId = $motTestData->site->id;
        $this->getRestClientMock('get', [ "data" => new SiteDto()], 'vehicle-testing-station/' . $siteId);

        $response = $this->getResultForAction('displayTestSummary', ['motTestNumber' => $motTestNr]);

        $this->assertEquals($expectedNonMotFlag, $response->isNonMotTest);
    }

    public function dataProviderForIsNonMotFlagOnTestSummaryResponse()
    {
        $isNonMotShouldBeTrue = true;
        $isNonMotShouldBeFalse = false;

        $userHasNonMotTestPermission = true;
        $userDoesNotHaveNonMotTestPermission = false;

        $testHasNonMotTestType = true;
        $testDoesNotHaveNonMotTestType = false;

        return [
            [$isNonMotShouldBeTrue, $userHasNonMotTestPermission, $testHasNonMotTestType],
            [$isNonMotShouldBeFalse, $userDoesNotHaveNonMotTestPermission, $testHasNonMotTestType],
            [$isNonMotShouldBeFalse, $userHasNonMotTestPermission, $testDoesNotHaveNonMotTestType]
        ];
    }

    public function testErrorWhenDisplayTestSummarySubmissionForNonMotTestDoesNotContainSite()
    {
        $mysteryShopperToggleEnabled = $userHasNonMotTestPermission = true;
        $this->setUpNonMotDependencies($mysteryShopperToggleEnabled, $userHasNonMotTestPermission);

        $motTestNr = (int) rand(1, 1000);
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;
        $motTestData->testTypeCode = MotTestTypeCode::NON_MOT_TEST;

        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->any())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $this->getFlashMessengerMockForAddErrorMessage(MotTestController::ERROR_NO_SITE_FOR_NON_MOT_TEST);

        $this->getResultForAction2('post', 'displayTestSummary', ['motTestNumber' => $motTestNr], null, []);
    }

    public function testNoErrorWhenDisplayTestSummarySubmissionForNonMotTestDoesContainSite()
    {
        $motTestNr = (int) rand(1, 1000);
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;
        $motTestData->testTypeCode = MotTestTypeCode::NON_MOT_TEST;

        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $this->getFlashMessengerMockForNoErrorMessage();

        $this->executeDisplayTestSummarySubmission($motTest, ['siteidentry' => 'VTS1234']);
    }

    private function executeDisplayTestSummarySubmission(MotTest $motTest, array $postData)
    {
        $mysteryShopperToggleEnabled = $userHasNonMotTestPermission = true;
        $this->setUpNonMotDependencies($mysteryShopperToggleEnabled, $userHasNonMotTestPermission);

        $this->getResultForAction2('post', 'displayTestSummary', ['motTestNumber' => $motTest->getMotTestNumber()], null, $postData);
    }

    public function testNoErrorWhenDisplayTestSummarySubmissionForNormalTestDoesNotContainSite()
    {
        $mysteryShopperToggleEnabled = $userHasNonMotTestPermission = true;
        $this->setUpNonMotDependencies($mysteryShopperToggleEnabled, $userHasNonMotTestPermission);

        $motTestNr = (int) rand(1, 1000);

        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->motTestNumber = $motTestNr;

        $motTest = new MotTest($motTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($motTestNr)
            ->will($this->returnValue($motTest));

        $this->getFlashMessengerMockForNoErrorMessage();

        $this->getResultForAction2('post', 'displayTestSummary', ['motTestNumber' => $motTestNr], null, []);
    }

    private function setUpNonMotDependencies($userHasNonMotTestPermission)
    {
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->any())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $permissions = [
            PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE,
            PermissionInSystem::MOT_TEST_CONFIRM
        ];
        if ($userHasNonMotTestPermission) {
            $permissions[] = PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM;
        }
        $this->setupAuthorizationService($permissions);

        $this->authServiceMock
            ->expects($this->any())
            ->method('isGranted')
            ->with(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM)
            ->willReturn($userHasNonMotTestPermission);
    }
}
