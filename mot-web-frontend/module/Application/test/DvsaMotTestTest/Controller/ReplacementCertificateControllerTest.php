<?php

namespace DvsaMotTestTest\Controller;

use Application\Helper\PrgHelper;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Dto\Vehicle\ModelDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilderWeb;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\ReplacementCertificateController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTestTest\TestHelper\Fixture;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Vehicle\Service\VehicleCatalogService;
use Zend\Session\Container;

/**
 * Class ReplacementCertificateControllerTest.
 */
class ReplacementCertificateControllerTest extends AbstractDvsaMotTestTestCase
{
    const EXAMPLE_MOT_TEST_NUMBER = 1;
    const EXAMPLE_DRAFT_ID = 5;

    /** @var VehicleCatalogService */
    private $vehicleCatalogService;

    /** @var MotFrontendAuthorisationServiceInterface */
    private $authorisationService;

    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $this->vehicleCatalogService = XMock::of(VehicleCatalogService::class);
        $this->vehicleCatalogService->expects($this->any())
            ->method('findMake')
            ->willReturn([]);

        $odometerViewObject = XMock::of(OdometerReadingViewObject::class);

        $this->controller = new ReplacementCertificateController(
            $this->vehicleCatalogService,
            $odometerViewObject
        );

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

        $this->givenIsAdmin(false);
        $this->routeMatch->setParam('id', self::EXAMPLE_DRAFT_ID);
        $this->routeMatch->setParam('motTestNumber', self::EXAMPLE_MOT_TEST_NUMBER);
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

    public static function dataProviderUpdateDraftActionToUpdateDataMapping()
    {
        return [
            ['updateVts', ['vtsSiteNumber' => 'SITE_NUMBER']],
            ['updateCertificate', ['reasonForReplacement' => 'REASON']],
            ['updateVin', ['vin' => 'THE_VIN']],
            ['updateVrm', ['vrm' => 'THEVRM']],
            ['updateColours', ['primaryColour' => 3, 'secondaryColour' => 4]],
            ['updateModel', ['make' => 5, 'model' => 6]],
            ['updateMake', ['make' => 5]],
            [
                'updateOdometer',
                [
                    'odometerReading' => [
                        'value' => 444,
                        'unit' => OdometerUnit::KILOMETERS,
                        'resultType' => OdometerReadingResultType::OK,
                    ],
                ],
            ],
            ['updateCor', ['countryOfRegistration' => 10]],
        ];
    }

    public function testReviewActionGivenTesterShouldDispatch()
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserIdDto(1);
                },
            ]
        );

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $this->givenPostAction('review');
    }

    public function testReviewActionGivenDifferentTesterShouldUpdateReason()
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserIdDto(5);
                },
            ]
        );

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $restClient->expects($this->any())->method('put')->with(
            $this->pathReplacementCertificateDraft(),
            ['reasonForDifferentTester' => 'REASON']
        );

        $this->givenPostAction('review', ['reasonForDifferentTester' => 'REASON']);
    }

    public function testReviewActionGivenAdminShouldDispatch()
    {
        $this->givenIsAdmin();
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserIdDto(1);
                },
            ]
        );

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $this->givenPostAction('review');
    }

    public function testReviewActionGivenDifferentTesterShouldReturnViewModelContainingRequiredProperties()
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserIdDto(5);
                },
            ]
        );

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

        $viewModel = $this->getResultForAction('review');
        $this->assertReviewViewModelProperties($viewModel);
    }

    public function testReviewActionGivenAdminShouldReturnViewModelContainingRequiredProperties()
    {
        $this->givenIsAdmin();
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserIdDto(5);
                },
            ]
        );

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

        $viewModel = $this->getResultForAction('review');
        $this->assertReviewViewModelProperties($viewModel);
    }

    public function testReviewActionGivenOriginalTesterShouldReturnViewModelContainingRequiredProperties()
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserIdDto(1);
                },
            ]
        );

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

        $viewModel = $this->getResultForAction('review');
        $this->assertReviewViewModelProperties($viewModel);
    }

    /**
     * Get from summary page, draft with provided Id not found (api), throw error (not found page).
     */
    public function testReviewGetActionGivenDraftDataThrow404ShouldRedirectTo404()
    {
        $this->setExpectedException(NotFoundException::class, 'Draft not found');

        $restClient = $this->getRestClientMockForServiceManager();
        $this->mockMethod(
            $restClient,
            'get',
            null,
            new NotFoundException('/', 'get', [], 10, 'Draft not found'),
            $this->pathReplacementCertificateDraft()
        );

        $this->getResultForAction('review');
    }

    public function testReplacementCertificateActionShowDraftGivenTesterReturnCorrectViewModel()
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserId(1);
                },
            ]
        );

        $testVehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->will($this->returnValue($testVehicleData));

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vars = $this->getResultForAction('replacementCertificate')->getVariables();

        $this->assertTesterShowDraftViewModelProperties($vars);
        $this->assertEquals($vars['isAdmin'], false);
    }

    public function testReplacementCertificateActionShowDraftGivenAdminReturnCorrectViewModel()
    {
        $this->givenIsAdmin();
        $restClient = $this->getRestClientMockForServiceManager();
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserId(1);
                },
            ]
        );

        $testVehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->will($this->returnValue($testVehicleData));

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vars = $this->getResultForAction('replacementCertificate')->getVariables();

        $assertVarsSet = $this->hasKeyAssertFactory($vars);
        $assertVarsSet(
            'vts',
            'vehicle',
            'countryOfRegistrationList'
        );

        $this->assertEquals($vars['isAdmin'], true);
        $this->assertTesterShowDraftViewModelProperties($vars);
    }

    /**
     * @dataProvider dataProviderUpdateDraftActionToUpdateDataMapping
     */
    public function testReplacementCertificateActionUpdateDraftCorrectUpdateDataSent($updateAction, $updateData)
    {
        $this->givenIsAdmin();
        $restClient = $this->getRestClientMockForServiceManager();

        $restClient->expects($this->any())->method('put')->with(
            $this->pathReplacementCertificateDraft(),
            $updateData
        );
        $this->givenRestClientReturningOnGet(
            $restClient,
            [
                $this->pathMotTest() => function () {
                    return self::restResponseMotTestWithUserId(1);
                },
                $this->pathReplacementDiff() => function () {
                    return self::asResponse([]);
                },
            ]
        );

        $this->givenPostAction(
            'replacementCertificate',
            array_merge(['action' => $updateAction], self::postDataUpdateDraft())
        );
    }

    public function testOdometerValidatorOnUpdatingCertificate()
    {
        $response = $this->givenPostAction(
            'replacementCertificate',
            array_merge(
                ['action' => ReplacementCertificateController::ACTION_UPDATE_ODOMETER],
                [
                    'odometerValue' => 9223372036854775807,
                    'odometerUnit' => OdometerUnit::KILOMETERS,
                    'odometerResultType' => OdometerReadingResultType::OK,
                ]
            )
        );

        $this->assertRedirectLocation($response,
            UrlBuilderWeb::replacementCertificate(self::EXAMPLE_DRAFT_ID, self::EXAMPLE_MOT_TEST_NUMBER));
    }

    /**
     * @dataProvider dataProviderTestVrmIsFixedOnUpdatingCertificate
     */
    public function testVrmIsFixedOnUpdatingCertificate($inputVrm, $expectedVrm)
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $spy = new MethodSpy($restClient, 'put');

        $this->givenPostAction(
            'replacementCertificate',
            array_merge(
                ['action' => ReplacementCertificateController::ACTION_UPDATE_VRM],
                [
                    'vrm' => $inputVrm,
                ]
            )
        );

        if ($expectedVrm != null) {
            /** @var \PHPUnit_Framework_MockObject_Invocation_Object $call */
            $call = $spy->getInvocations()[0];
            $this->assertEquals($expectedVrm, $call->parameters[1]['vrm']);
        } else {
            $call = $spy->getInvocations();
            $this->assertEmpty($call);
        }
    }

    public function dataProviderTestVrmIsFixedOnUpdatingCertificate()
    {
        return [
            ['123 fta', '123FTA'],
            ['-*[]123 fta<>\-', null],
            ["123\tabc", '123ABC'],
        ];
    }

    /**
     * Check for double post.
     */
    public function testReviewDoublePost()
    {
        $tokenGuid = 'testToken';

        $session = new Container('prgHelperSession');
        $session->offsetSet($tokenGuid, 'redirectUrl');

        $postParams = [
            PrgHelper::FORM_GUID_FIELD_NAME => $tokenGuid,
        ];
        $this->getResultForAction2('post', 'review', null, null, $postParams);

        $this->assertRedirectLocation2('redirectUrl');
    }

    private static function restResponseDraft()
    {
        return self::asResponse(
            [
                'primaryColour' => ['id' => 4, 'name' => 'Yellow'],
                'secondaryColour' => null,
                'odometerReading' => [
                    'value' => 1234,
                    'unit' => OdometerUnit::KILOMETERS,
                    'resultType' => OdometerReadingResultType::OK,
                ],
                'vin' => '12345678901234567',
                'vrm' => 'ABD3523',
                'countryOfRegistration' => ['id' => 4, 'name' => 'France'],
                'model' => ['id' => 5, 'code' => 'C100', 'name' => 'C4'],
                'make' => ['id' => 1, 'code' => 'C200', 'name' => 'Citroen'],
                'expiryDate' => '2015-02-02',
                'motTestNumber' => self::EXAMPLE_MOT_TEST_NUMBER,
                'vts' => [
                    'siteNumber' => '32323',
                    'address' => [
                        'line1' => '',
                        'line2' => '',
                        'line3' => '',
                        'line4' => '',
                        'town' => '',
                        'postcode' => '',
                        'country' => '',
                    ],
                    'name' => 'vts',
                ],
            ]
        );
    }

    private static function restResponseMotTestWithUserId($testerUserId)
    {
        return self::asResponse(
            [
                'tester' => (new PersonDto())->setId($testerUserId),
                'motTestNumber' => self::EXAMPLE_MOT_TEST_NUMBER,
                'primaryColour' => new ColourDto(),
                'secondaryColour' => new ColourDto(),
                'make' => new MakeDto(),
                'model' => new ModelDto(),
                'countryOfRegistration' => new CountryDto(),
            ]
        );
    }

    private static function restResponseMotTestWithUserIdDto($testerUserId)
    {
        return self::asResponse(
            (new MotTestDto())
                ->setTester((new PersonDto())->setId($testerUserId))
                ->setMotTestNumber(self::EXAMPLE_MOT_TEST_NUMBER)
                ->setPrimaryColour(new ColourDto())
                ->setSecondaryColour(new ColourDto())
                ->setMake(new MakeDto())
                ->setModel(new ModelDto())
                ->setCountryOfRegistration(new CountryDto())
        );
    }

    private static function differentTesterReasons()
    {
        return self::asResponse(
            [
                ['code' => 'x', 'description' => 'y'],
            ]
        );
    }

    private static function postDataUpdateDraft()
    {
        return [
            'vts' => 'SITE_NUMBER',
            'reasonForReplacement' => 'REASON',
            'vin' => 'THE_VIN',
            'vrm' => 'THEVRM',
            'primaryColour' => 3,
            'secondaryColour' => 4,
            'make' => 5,
            'model' => 6,
            'odometerValue' => 444,
            'odometerUnit' => OdometerUnit::KILOMETERS,
            'odometerResultType' => OdometerReadingResultType::OK,
            'cor' => 10,
            'expiryDate-day' => '4',
            'expiryDate-month' => '12',
            'expiryDate-year' => '2014',
        ];
    }

    private static function asResponse($json)
    {
        return ['data' => $json];
    }

    /**
     * @param $array
     *
     * @return callable
     */
    private function hasKeyAssertFactory(&$array)
    {
        return function () use (&$array) {
            $funcArgs = func_get_args();
            foreach ($funcArgs as $key) {
                $this->assertArrayHasKey($key, $array);
            }
        };
    }

    private function givenIsAdmin($decision = true)
    {
        $grantedPermissions = [PermissionInSystem::CERTIFICATE_REPLACEMENT];
        if ($decision) {
            $grantedPermissions [] = PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS;
        }

        $this->setupAuthorizationService($grantedPermissions);
    }

    /**
     * @param MockObj $restClient
     * @param array   $extensionUrl2CallbackMap
     */
    private function givenRestClientReturningOnGet($restClient, $extensionUrl2CallbackMap = [])
    {
        $baseUrl2CallbackMap = [
            $this->pathReplacementCertificateDraft() => function () {
                return self::restResponseDraft();
            },
            $this->pathDifferentTesterReasons() => function () {
                return self::differentTesterReasons();
            },
            $this->pathOdometerCheck() => function () {
                return self::asResponse(['modifiable' => true]);
            },
        ];

        // merge extra entries
        $url2CallbackMap = array_merge($baseUrl2CallbackMap, $extensionUrl2CallbackMap);
        $restClient->expects($this->any())->method('get')
            ->willReturnCallback(
                function ($arg) use (&$url2CallbackMap) {
                    return $url2CallbackMap[(string) $arg]();
                }
            );
    }

    /**
     * @param int $id
     *
     * @return string
     */
    private function pathReplacementCertificateDraft(
        $id = self::EXAMPLE_DRAFT_ID,
        $motTestNumber = self::EXAMPLE_MOT_TEST_NUMBER
    ) {
        return UrlBuilder::replacementCertificateDraft($id, $motTestNumber)->toString();
    }

    /**
     * @return string
     */
    private function pathMotTest()
    {
        return MotTestUrlBuilder::motTest(self::EXAMPLE_MOT_TEST_NUMBER)->toString();
    }

    private function pathDifferentTesterReasons()
    {
        return 'cert-change-diff-tester-reason';
    }

    private function pathOdometerCheck()
    {
        return MotTestUrlBuilder::odometerReadingModifyCheck(self::EXAMPLE_MOT_TEST_NUMBER)->toString();
    }

    /**
     * @return string
     */
    private function pathReplacementDiff()
    {
        return UrlBuilder::replacementCertificateDraftDiff(self::EXAMPLE_DRAFT_ID,
            self::EXAMPLE_MOT_TEST_NUMBER)->toString();
    }

    private function givenPostAction($action, $postParams = [])
    {
        return $this->getResultForAction2('post', $action, null, null, $postParams);
    }

    /**
     * @param \Zend\View\Model\ViewModel $vm
     */
    private function assertReviewViewModelProperties($vm)
    {
        $vars = $vm->getVariables();
        $assertVars = $this->hasKeyAssertFactory($vars);
        $assertVars('motTest', 'odometerReading', 'isOriginalTester', 'differentTesterReasons', 'isAdmin');
    }

    private function assertTesterShowDraftViewModelProperties($vars)
    {
        $assertVars = $this->hasKeyAssertFactory($vars);
        $assertVars('odometerReading', 'colours');
    }
}
