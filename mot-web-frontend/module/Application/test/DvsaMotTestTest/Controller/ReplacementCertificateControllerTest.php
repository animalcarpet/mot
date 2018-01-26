<?php

namespace DvsaMotTestTest\Controller;

use Application\Helper\PrgHelper;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Resource\Item\Site;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Dto\Vehicle\ModelDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\UrlBuilder\UrlBuilderWeb;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\ReplacementCertificateController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTest\Service\ReplacementCertificateDraftService;
use DvsaMotTestTest\TestHelper\Fixture;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Vehicle\Service\VehicleCatalogService;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * Class ReplacementCertificateControllerTest.
 */
class ReplacementCertificateControllerTest extends AbstractDvsaMotTestTestCase
{
    const EXAMPLE_MOT_TEST_NUMBER = 1;
    const EXAMPLE_DRAFT_ID = 5;
    const DRAFT_PRIMARY_COLOUR = 'Yellow';
    const DRAFT_SECONDARY_COLOUR = null;

    /** @var VehicleCatalogService */
    private $vehicleCatalogService;

    /** @var ReplacementCertificateDraftService|MockObj */
    private $replacementCertificateDraftService;

    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $this->vehicleCatalogService = XMock::of(VehicleCatalogService::class);
        $this->vehicleCatalogService->expects($this->any())
            ->method('findMake')
            ->willReturn([]);

        $odometerViewObject = XMock::of(OdometerReadingViewObject::class);

        $this->replacementCertificateDraftService = XMock::of(ReplacementCertificateDraftService::class);

        $this->replacementCertificateDraftService
            ->expects($this->any())
            ->method('getDraft')
            ->willReturn(self::restResponseDraft()['data']);
        $this->replacementCertificateDraftService
            ->expects($this->any())
            ->method('getChangeOfTesterReasons')
            ->willReturn(self::differentTesterReasons()['data']);

        $this->controller = new ReplacementCertificateController(
            $this->replacementCertificateDraftService,
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
        $this->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)));

        $this->givenPostAction('review');
    }

    public function testReviewActionGivenDifferentTesterShouldUpdateReason()
    {
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->tester->id = 5;

        $motTest = new MotTest($motTestData);

        $this->withMotTest($motTest);

        $this->replacementCertificateDraftService
            ->expects($this->once())
            ->method('updateDraftReasonForDifferentTester')
            ->with($this->anything(), $this->anything(), 'REASON');

        $this->givenPostAction('review', ['reasonForDifferentTester' => 'REASON']);
    }

    public function testReviewActionGivenAdminShouldDispatch()
    {
        $this
            ->givenIsAdmin()
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)));

        $this->givenPostAction('review');
    }

    public function testReviewActionGivenDifferentTesterShouldReturnViewModelContainingRequiredProperties()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester(2));

        $this
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $viewModel = $this->getResultForAction('review');
        $this->assertReviewViewModelProperties($viewModel);

        $this->assertNull($viewModel->motVTSDraft);
        $this->assertEquals(self::DRAFT_PRIMARY_COLOUR, $viewModel->vehicleViewModel->getColour()->getName());
        $this->assertEquals(self::DRAFT_SECONDARY_COLOUR, $viewModel->vehicleViewModel->getColourSecondary()->getName());
    }

    public function testReviewActionGivenAdminShouldReturnViewModelContainingRequiredProperties()
    {
        $this
            ->givenIsAdmin()
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $viewModel = $this->getResultForAction('review');
        $this->assertReviewViewModelProperties($viewModel);

        $this->assertInstanceOf(Site::class, $viewModel->motVTSDraft);
        $this->assertEquals(self::DRAFT_PRIMARY_COLOUR, $viewModel->vehicleViewModel->getColour()->getName());
        $this->assertEquals(self::DRAFT_SECONDARY_COLOUR, $viewModel->vehicleViewModel->getColourSecondary()->getName());
    }

    public function testReviewActionGivenOriginalTesterShouldReturnViewModelContainingRequiredProperties()
    {
        $this
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $viewModel = $this->getResultForAction('review');
        $this->assertReviewViewModelProperties($viewModel);

        $this->assertNull($viewModel->motVTSDraft);
        $this->assertEquals(self::DRAFT_PRIMARY_COLOUR, $viewModel->vehicleViewModel->getColour()->getName());
        $this->assertEquals(self::DRAFT_SECONDARY_COLOUR, $viewModel->vehicleViewModel->getColourSecondary()->getName());
    }

    public function testReplacementCertificateActionShowDraftGivenTesterReturnCorrectViewModel()
    {
        $this
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $vars = $this->getResultForAction('replacementCertificate')->getVariables();

        $this->assertTesterShowDraftViewModelProperties($vars);
        $this->assertEquals($vars['isAdmin'], false);
    }

    public function testReplacementCertificateActionShowDraftGivenAdminReturnCorrectViewModel()
    {
        $this
            ->givenIsAdmin()
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

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

    public function testReplacementCertificateActionShowDraftGivenDifferentTesterReturnCorrectViewModel()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester(2));

        $this
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $vars = $this->getResultForAction('replacementCertificate')->getVariables();

        $this->assertTesterShowDraftViewModelProperties($vars);
        $this->assertEquals($vars['isAdmin'], false);
    }

    /**
     * @dataProvider dataProviderUpdateDraftActionToUpdateDataMapping
     */
    public function testReplacementCertificateActionUpdateDraftCorrectUpdateDataSent($updateAction, $updateData)
    {
        $this->givenIsAdmin();

        $this->replacementCertificateDraftService
            ->expects($this->once())
            ->method('updateDraft')
            ->with($this->anything(), $this->anything(), $updateData);

        $this->givenPostAction(
            'replacementCertificate',
            array_merge(['action' => $updateAction], self::postDataUpdateDraft())
        );
    }

    public function testUpdateData()
    {
        $this->givenIsAdmin();

        $this->replacementCertificateDraftService
            ->expects($this->once())
            ->method('updateDraft')
            ->with($this->anything(), $this->anything(), ['vtsSiteNumber' => 'SITE_NUMBER']);

        $this->givenPostAction(
            'replacementCertificate',
            array_merge(['action' => 'updateVts'], self::postDataUpdateDraft())
        );
    }

    public function testFinishActionAfterUpdatingCertificate()
    {
        $motTest = new MotTest(Fixture::getMotTestDataVehicleClass4(true));
        $vehicle = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $this
            ->withMotTest($motTest)
            ->withDvsaVehicle($vehicle);

        $viewModel = $this->getResultForAction('finish', ['motTestNumber' => 1]);
        
        $this->assertEquals($vehicle->getRegistration(), $viewModel->vehicleRegistration);
        $this->assertEquals($motTest->getMotTestNumber(), $viewModel->motTestNumber);
    }

    public function testOtherVehicleActionIsNotAdmin()
    {
        $this->expectException(UnauthorisedException::class);

        $this->getResultForAction('otherVehicle', ['motTestNumber' => 1]);
    }

    public function testOtherVehicleActionHappy()
    {
        $this
            ->givenIsAdmin()
            ->withMotTest(new MotTest(Fixture::getMotTestDataVehicleClass4(true)))
            ->withDvsaVehicle(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $viewModel = $this->getResultForAction('otherVehicle', ['motTestNumber' => 1]);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->isAdmin);
    }

    public function testOtherVehicleActionShouldRedirectToDraftOnSuccessfulPostRequest()
    {
        $this->givenIsAdmin();

        $postParams = [
            'action' => 'updateCertificate',
            'reasonForReplacement' => 'REASON'
        ];

        $this->getResultForAction2('post', 'otherVehicle', ['motTestNumber' => 1], null, $postParams);

        $this->assertRedirectLocation2(UrlBuilderWeb::replacementCertificate(self::EXAMPLE_DRAFT_ID, self::EXAMPLE_MOT_TEST_NUMBER));
    }

    public function testFinishActionThrows404WhenMotTestNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('MOT details not found');

        $this->withNoMotTest(123);

        $this->getResultForAction('finish', ['motTestNumber' => 123]);
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

        $this->assertRedirectLocation(
            $response,
            UrlBuilderWeb::replacementCertificate(self::EXAMPLE_DRAFT_ID, self::EXAMPLE_MOT_TEST_NUMBER)
        );
    }

    /**
     * @dataProvider dataProviderTestVrmIsFixedOnUpdatingCertificate
     */
    public function testVrmIsFixedOnUpdatingCertificate($inputVrm, $expectedVrm)
    {
        $spy = new MethodSpy($this->replacementCertificateDraftService, 'updateDraft');

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
            $this->assertEquals($expectedVrm, $call->parameters[2]['vrm']);
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
                'primaryColour' => ['id' => 4, 'name' => self::DRAFT_PRIMARY_COLOUR],
                'secondaryColour' => self::DRAFT_SECONDARY_COLOUR,
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
                        'addressLine1' => '',
                        'addressLine2' => '',
                        'addressLine3' => '',
                        'addressLine4' => '',
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

        return $this;
    }

    private function givenPostAction($action, $postParams = [])
    {
        return $this->getResultForAction2('post', $action, null, null, $postParams);
    }

    private function withMotTest(MotTest $motTest, $id = 1)
    {
        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($id)
            ->willReturn($motTest);

        return $this;
    }

    private function withNoMotTest($id = 1)
    {
        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with($id)
            ->willReturn(null);

        return $this;
    }

    private function withDvsaVehicle(DvsaVehicle $dvsaVehicle)
    {
        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->will($this->returnValue($dvsaVehicle));

        return $this;
    }

    /**
     * @param \Zend\View\Model\ViewModel $vm
     */
    private function assertReviewViewModelProperties($vm)
    {
        $vars = $vm->getVariables();
        $assertVars = $this->hasKeyAssertFactory($vars);
        $assertVars('motTest', 'odometerReading', 'isOriginalTester', 'differentTesterReasons', 'isAdmin', 'motVTSDraft');
    }

    private function assertTesterShowDraftViewModelProperties($vars)
    {
        $assertVars = $this->hasKeyAssertFactory($vars);
        $assertVars('odometerReading', 'colours');
    }
}
