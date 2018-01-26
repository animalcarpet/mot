<?php

namespace DvsaMotTestTest\Action\BrakeTestResults;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Resource\Item\VehicleClass;
use Dvsa\Mot\ApiClient\Resource\Item\WeightSource;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Dto\BrakeTest\BrakeTestTypeDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Model\BrakeTestConfigurationClass3AndAboveHelper;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use PHPUnit_Framework_MockObject_Matcher_InvokedRecorder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class ViewBrakeTestConfigurationActionTest extends TestCase
{
    const FORM_VALIDATION_ERROR = 'Error: Form validation error';
    const DEFAULT_SERVICE_BRAKE_TEST_TYPE = 'DefaultServiceBrakeTestType';
    const DEFAULT_SERVICE_BRAKE_TEST_TYPE_CODE = 'DefaultServiceBrakeTestTypeCode';
    const DEFAULT_PARKING_BRAKE_TEST_TYPE = 'DefaultParkingBrakeTestType';
    const DEFAULT_PARKING_BRAKE_TEST_TYPE_CODE = 'DefaultParkingBrakeTestTypeCode';
    const VEHICLE_WEIGHT = 1111;

    /**
     * @var stdClass
     */
    private $motTestData;

    /**
     * @var DvsaVehicle|MockObject
     */
    private $mockDvsaVehicle;

    /**
     * @var VehicleService|MockObject
     */
    private $mockVehicleService;

    /**
     * @var MotTestService|MockObject
     */
    private $mockMotTestService;

    /**
     * @var CatalogService|MockObject
     */
    private $mockCatalogService;

    /**
     * @var Client|MockObject
     */
    private $mockRestClient;

    /**
     * @var OfficialWeightSourceForVehicle|MockObject
     */
    private $officialWeightSourceForVehicle;

    /**
     * @var BrakeTestConfigurationClass3AndAboveMapper
     */
    private $brakeTestConfigurationClass3AndAboveMapper;

    public function setUp()
    {
        $this->motTestData = new stdClass();
        $this->motTestData->motTestNumber = 295116285800;
        $this->motTestData->vehicleId = 1;
        $this->motTestData->vehicleVersion = 1;
        $this->motTestData->status = MotTestStatusName::ACTIVE;
        $this->motTestData->vehicleClass = new stdClass();
        $this->motTestData->vehicleWeight = 10000;
        $this->motTestData->testTypeCode = MotTestTypeCode::NORMAL_TEST;
        $this->motTestData->previousTestVehicleWeight = 9999;
        $this->motTestData->brakeTestResult = new stdClass();
        $this->motTestData->brakeTestResult->brakeTestTypeCode = 1;
        $this->motTestData->brakeTestResult->vehicleWeightFront = 600;
        $this->motTestData->brakeTestResult->vehicleWeightRear = 600;
        $this->motTestData->brakeTestResult->riderWeight = 100;
        $this->motTestData->brakeTestResult->isSidecarAttached = false;
        $this->motTestData->brakeTestResult->sidecarWeight = 300;
        $this->motTestData->brakeTestResult->vehicleWeight = 1;
        $this->motTestData->brakeTestResult->serviceBrakeIsSingleLine = false;
        $this->motTestData->brakeTestResult->numberOfAxles = 4;
        $this->motTestData->brakeTestResult->parkingBrakeNumberOfAxles = 2;
        $this->motTestData->brakeTestResult->weightType = '';
        $this->motTestData->brakeTestResult->serviceBrake1TestType = '';
        $this->motTestData->brakeTestResult->serviceBrake2TestType = '';
        $this->motTestData->brakeTestResult->parkingBrakeTestType = '';
        $this->motTestData->brakeTestResult->weightIsUnladen = false;
        $this->motTestData->brakeTestResult->commercialVehicle = false;
        $this->motTestData->brakeTestResult->singleInFront = false;

        $this->mockDvsaVehicle = XMock::of(DvsaVehicle::class);
        $this->mockMotTestService = XMock::of(MotTestService::class);
        $this->mockVehicleService = XMock::of(VehicleService::class);
        $this->mockCatalogService = XMock::of(CatalogService::class);
        $this->mockRestClient = XMock::of(Client::class);

        $this->officialWeightSourceForVehicle = XMock::of(OfficialWeightSourceForVehicle::class);

        $this->brakeTestConfigurationClass3AndAboveMapper = new BrakeTestConfigurationClass3AndAboveMapper(
            $this->officialWeightSourceForVehicle
        );
    }

    /**
     * @param $specValue
     * @param $specInvocations
     *
     * @dataProvider featureToggleAndSpecificationWontBeCalledDP
     */
    public function testRedirectWithErrorMessageWhenMotTestIsNotActive(
        $specValue,
        $specInvocations
    )
    {
        $this->withOfficialWeightSourceSpec($specValue, $specInvocations);

        $this->withMotTestStatus(MotTestStatusName::PASSED);

        $action = $this->buildAction();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute(1);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals(MotTestController::ROUTE_MOT_TEST, $actionResult->getRouteName());
        $this->assertContains(
            InvalidTestStatus::ERROR_MESSAGE_TEST_COMPLETE,
            $actionResult->getErrorMessages()
        );
    }

    /**
     * @dataProvider dataProviderTestCorrectViewIsDisplayed
     *
     * @param string $vehicleClassCode
     * @param string $template
     * @param $specValue
     * @param $specInvocations
     */
    public function testCorrectViewIsDisplayed(
        $vehicleClassCode,
        $template,
        $specValue,
        $specInvocations
    )
    {
        $this->withOfficialWeightSourceSpec($specValue, $specInvocations);

        $this->mockMethods($vehicleClassCode);

        $action = $this->buildAction();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute(1);

        $this->assertEquals($template, $actionResult->getTemplate());
    }

    /**
     * @return array
     */
    public function dataProviderTestCorrectViewIsDisplayed()
    {
        return [
            // class, expected template, specValue, specInvocations
            ['1', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_1_2, true, 0],
            ['1', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_1_2, false, 0],

            ['2', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_1_2, true, 0],
            ['2', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_1_2, false, 0],

            ['3', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, true, 1],
            ['3', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, false, 1],

            ['4', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, true, 1],
            ['4', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, false, 1],

            ['5', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, true, 1],
            ['5', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, false, 1],

            ['7', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, true, 1],
            ['7', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE, false, 1],
        ];
    }

    /**
     * @param $vehicleClass
     * @param $specValue
     * @param $specInvocations
     *
     * @dataProvider testGroupBDefaultValuesAreSetClass3AndAboveDP
     */
    public function testGroupBDefaultValuesAreSet(
        $vehicleClass,
        $specValue,
        $specInvocations
    )
    {
        $this->mockMethods($vehicleClass);
        $this->withOfficialWeightSourceSpec($specValue, $specInvocations);

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        /** @var BrakeTestConfigurationClass3AndAboveHelper $configHelper */
        $configHelper = $actionResult->getViewModel()->configHelper;

        $this->assertEquals(
            $configHelper->getServiceBrakeLineType(),
            BrakeTestConfigurationClass3AndAboveHelper::BRAKE_LINE_TYPE_DUAL);
        $this->assertEquals(
            $configHelper->getNumberOfAxles(),
            $this->motTestData->brakeTestResult->numberOfAxles);
        $this->assertEquals(
            $configHelper->getParkingBrakeNumberOfAxles(),
            $this->motTestData->brakeTestResult->parkingBrakeNumberOfAxles);
        $this->assertEquals(
            $configHelper->getWeightType(),
            $this->motTestData->brakeTestResult->weightType);
        $this->assertEquals(
            $configHelper->getServiceBrakeTestType(),
            $this->motTestData->brakeTestResult->serviceBrake1TestType);
        $this->assertEquals(
            $configHelper->getParkingBrakeTestType(),
            $this->motTestData->brakeTestResult->parkingBrakeTestType);
        $this->assertEquals(
            $configHelper->getVehicleWeight(),
            $this->motTestData->brakeTestResult->vehicleWeight);
        $this->assertEquals(
            $configHelper->getWeightIsUnladen(),
            $this->motTestData->brakeTestResult->weightIsUnladen);
        $this->assertEquals(
            $configHelper->getVehiclePurposeType(),
            BrakeTestConfigurationClass3AndAboveHelper::PURPOSE_PERSONAL);
        $this->assertEquals(
            $configHelper->isSingleWheelInFront(),
            $this->motTestData->brakeTestResult->singleInFront
        );
    }

    public function testGroupBDefaultValuesAreSetClass3AndAboveDP()
    {
        return [
            // vehicleClass, specValue, specIC
            [3, true, 1],
            [3, false, 1],

            [4, true, 1],
            [4, false, 1],

            [5, true, 1],
            [5, false, 1],

            [7, true, 1],
            [7, false, 1],
        ];
    }

    public function testDtoPopulatedAndErrorMessagesDisplayFromPreviousAction()
    {
        $this->mockMethods();

        $action = $this->buildAction()->setPreviousActionResult(
            (new ViewActionResult())->addErrorMessage(self::FORM_VALIDATION_ERROR),
            [
                'serviceBrake1TestType' => 'ROLLR',
                'parkingBrakeTestType' => 'ROLLR',
                'vehicleWeight' => null,
                'brakeLineType' => 'dual',
                'numberOfAxles' => '2',
                'parkingBrakeNumberOfAxles' => '1',
                'vehicleClass' => '4',
            ]
        );

        $actionResult = $action->execute(1);

        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeTestType(),
        'ROLLR');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeTestType(),
        'ROLLR');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getVehicleWeight(),
            '');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeLineType(),
            'dual');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getNumberOfAxles(),
            '2');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeNumberOfAxles(),
            '1');
        $this->assertEquals($actionResult->getViewModel()->configHelper->locksApplicableToFirstServiceBrake(),
            true);
        $this->assertContains(
            self::FORM_VALIDATION_ERROR,
            $actionResult->getErrorMessages()
        );
    }

    /**
     * @dataProvider dataProviderTestPreselectedTestWeight
     *
     * @param $toggleValue
     * @param $toggleInvocations
     * @param $specValue
     * @param $specInvocations
     * @param int $vehicleWeight
     * @param int $previousTestVehicleWeight
     * @param string $serviceBrake1TestType
     * @param string $parkingBrakeTestType
     * @param bool $expected
     */
    public function testPreselectedTestWeightForGroupBVehicle(
        $specValue,
        $specInvocations,
        $vehicleWeight,
        $previousTestVehicleWeight,
        $serviceBrake1TestType,
        $parkingBrakeTestType,
        $expected
    ) {
        $this->markTestSkipped('Needs further investigation to refactor test now that feature toggle is always on in prod.');
        $this->withOfficialWeightSourceSpec($specValue, $specInvocations);
        $this->mockMethods();

        $this->withBrakeTestResults(
            $vehicleWeight,
            $previousTestVehicleWeight,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        );

        $action = $this->buildAction();

        $actionResult = $action->execute(1);

        $actualPreselectValue = $actionResult->getViewModel()->getVariables()['preselectBrakeTestWeight'];
        $this->assertEquals($expected, $actualPreselectValue);
    }

    /**
     * @return array
     */
    public function dataProviderTestPreselectedTestWeight()
    {
        return [
            //$toggleValue $toggleInvocations $specValue $specInvocations $vehicleWeight $previousTestVehicleWeight $serviceBrake1TestType $parkingBrakeTestType $expected
            // FT = false => specification not used => old logic
            [true, 0, 10000, 9999, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::ROLLER, true],
            [true, 0, 10000, 9999, BrakeTestTypeCode::PLATE, BrakeTestTypeCode::PLATE, true],
            [true, 0, 10000, 9999, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::DECELEROMETER, true],
            [true, 0, 10000, 9999, BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::DECELEROMETER, false],
            [true, 0, 10000, null, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::ROLLER, true],
            [true, 0, null, 9999, BrakeTestTypeCode::PLATE, BrakeTestTypeCode::PLATE, true],
            [true, 0, null, null, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::ROLLER, true],
            [true, 0, null, 9999, BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::DECELEROMETER, false],
            [true, 0, null, null, BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::DECELEROMETER, false],
        ];
    }


    /**
     * @param $vehicleClass
     * @param $vehicleWeightExists
     * @param $vehicleWeightType
     * @param $brakeTestResultWeightType
     * @param $expectedSelectedWeightType
     * @dataProvider dataProviderTestGroupBCorrectWeightTypeIsSelected
     */
    public function testGroupBCorrectWeightTypeIsSelected(
        $vehicleClass,
        $vehicleWeightExists,
        $vehicleWeightType,
        $brakeTestResultWeightType,
        $expectedSelectedWeightType
    )
    {
        //for testing which Weight Source is selected on list we use the real OfficalWeightSource classifier
        //it makes those classes coupled but we can check that when logic of this class changes, wrong value will be
        //selected in action
        $this->withRealOfficialWeightSourceForVehicle();
        $this->mockMethods($vehicleClass);

        if($brakeTestResultWeightType == null) {
            $this->withEmptyBrakeTestResult();
            //site is needed to load defaults when there's no BrakeTestResult
            $this->withSite();
        } else {
            $this->withBrakeTestServiceBrake1TestType(BrakeTestTypeCode::PLATE);
            $this->withBrakeTestParkinbgBrakeTestType(BrakeTestTypeCode::PLATE);
            $this->withBrakeTestWeightType($brakeTestResultWeightType);
        }

        if($vehicleWeightExists) {
            $this->withVehicleWeight(self::VEHICLE_WEIGHT);
        }

        if($vehicleWeightType != null) {
            if($expectedSelectedWeightType != null) {
                $this->withVehicleWeightType($vehicleWeightType);
            }
        }

        $action = $this->buildAction();

        $actionResult = $action->execute(1);

        $actualPreselectValue = $actionResult->getViewModel()->getVariable('selectedWeightType');
        $this->assertEquals($expectedSelectedWeightType, $actualPreselectValue);
    }

    public function dataProviderTestGroupBCorrectWeightTypeIsSelected()
    {
        return [
            //EDIT PAGE - brake test results exist, we select anything is picked if it matches list for given class
            //class 3
            [VehicleClassCode::CLASS_3, false, null, WeightSourceCode::VSI, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_3, false, null, WeightSourceCode::MISW, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_3, false, null, WeightSourceCode::ORD_MISW, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_3, false, null, WeightSourceCode::PRESENTED, WeightSourceCode::PRESENTED],
            [VehicleClassCode::CLASS_3, false, null, WeightSourceCode::NOT_APPLICABLE, WeightSourceCode::NOT_APPLICABLE],
            //class 4
            [VehicleClassCode::CLASS_4, false, null, WeightSourceCode::VSI, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, false, null, WeightSourceCode::MISW, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, false, null, WeightSourceCode::ORD_MISW, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, false, null, WeightSourceCode::PRESENTED, WeightSourceCode::PRESENTED],
            [VehicleClassCode::CLASS_4, false, null, WeightSourceCode::NOT_APPLICABLE, WeightSourceCode::NOT_APPLICABLE],
            //class 5
            [VehicleClassCode::CLASS_5, false, null, WeightSourceCode::DGW, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, false, null, WeightSourceCode::DGW_MAM, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, false, null, WeightSourceCode::VSI, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, false, null, WeightSourceCode::ORD_DGW_MAM, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, false, null, WeightSourceCode::CALCULATED, WeightSourceCode::CALCULATED],
            //class 7
            [VehicleClassCode::CLASS_7, false, null, WeightSourceCode::DGW, WeightSourceCode::DGW],
            [VehicleClassCode::CLASS_7, false, null, WeightSourceCode::VSI, WeightSourceCode::DGW],
            [VehicleClassCode::CLASS_7, false, null, WeightSourceCode::ORD_DGW, WeightSourceCode::DGW],
            [VehicleClassCode::CLASS_7, false, null, WeightSourceCode::PRESENTED, WeightSourceCode::PRESENTED],

            //CREATE PAGE - brake test result doesn't exist, Vehicle Weight Source is set but Vehicle Weight is empty => we select nothing
            //TODO before merge, maybe remove checking all possible WeightSourceCodes for null being selected, as we just need one to confirm this case is working
            //class 3
            [VehicleClassCode::CLASS_3, false, WeightSourceCode::VSI, null, null],
            [VehicleClassCode::CLASS_3, false, WeightSourceCode::MISW, null, null],
            [VehicleClassCode::CLASS_3, false, WeightSourceCode::ORD_MISW, null, null],
            [VehicleClassCode::CLASS_3, false, WeightSourceCode::PRESENTED, null, null],
            [VehicleClassCode::CLASS_3, false, WeightSourceCode::NOT_APPLICABLE, null, null],
            //class 4
            [VehicleClassCode::CLASS_4, false, WeightSourceCode::VSI, null, null],
            [VehicleClassCode::CLASS_4, false, WeightSourceCode::MISW, null, null],
            [VehicleClassCode::CLASS_4, false, WeightSourceCode::ORD_MISW, null, null],
            [VehicleClassCode::CLASS_4, false, WeightSourceCode::PRESENTED, null, null],
            [VehicleClassCode::CLASS_4, false, WeightSourceCode::NOT_APPLICABLE, null, null],
            //class 5
            [VehicleClassCode::CLASS_5, false, WeightSourceCode::DGW, null, null],
            [VehicleClassCode::CLASS_5, false, WeightSourceCode::DGW_MAM, null, null],
            [VehicleClassCode::CLASS_5, false, WeightSourceCode::VSI, null, null],
            [VehicleClassCode::CLASS_5, false, WeightSourceCode::ORD_DGW_MAM, null, null],
            [VehicleClassCode::CLASS_5, false, WeightSourceCode::CALCULATED, null, null],
            //class 7
            [VehicleClassCode::CLASS_7, false, WeightSourceCode::DGW, null, null],
            [VehicleClassCode::CLASS_7, false, WeightSourceCode::VSI, null, null],
            [VehicleClassCode::CLASS_7, false, WeightSourceCode::ORD_DGW, null, null],
            [VehicleClassCode::CLASS_7, false, WeightSourceCode::PRESENTED, null, null],

            //CREATE PAGE - brake test result doesn't exist, we select first value only when Weight Source is official
            //tests that ORD's in BrakeTestResult are being mapped to the first option in view
            [VehicleClassCode::CLASS_3, true, WeightSourceCode::VSI, null, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, true, WeightSourceCode::MISW, null, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_3, true, WeightSourceCode::ORD_MISW, null, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_3, true, WeightSourceCode::PRESENTED, null, null],
            [VehicleClassCode::CLASS_3, true, WeightSourceCode::NOT_APPLICABLE, null, null],
            //class 4
            [VehicleClassCode::CLASS_4, true, WeightSourceCode::VSI, null, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, true, WeightSourceCode::MISW, null, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, true, WeightSourceCode::ORD_MISW, null, WeightSourceCode::VSI],
            [VehicleClassCode::CLASS_4, true, WeightSourceCode::PRESENTED, null, null],
            [VehicleClassCode::CLASS_4, true, WeightSourceCode::NOT_APPLICABLE, null, null],
            //class 5
            [VehicleClassCode::CLASS_5, true, WeightSourceCode::DGW, null, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, true, WeightSourceCode::DGW_MAM, null, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, true, WeightSourceCode::VSI, null, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, true, WeightSourceCode::ORD_DGW_MAM, null, WeightSourceCode::DGW_MAM],
            [VehicleClassCode::CLASS_5, true, WeightSourceCode::CALCULATED, null, null],
            //class 7
            [VehicleClassCode::CLASS_7, true, WeightSourceCode::DGW, null, WeightSourceCode::DGW],
            [VehicleClassCode::CLASS_7, true, WeightSourceCode::VSI, null, WeightSourceCode::DGW],
            [VehicleClassCode::CLASS_7, true, WeightSourceCode::ORD_DGW, null, WeightSourceCode::DGW],
            [VehicleClassCode::CLASS_7, true, WeightSourceCode::PRESENTED, null, null],
        ];
    }

    /**
     * @param $vehicleWeightType
     * @param $vehicleWeight
     * @dataProvider dataProviderTestGroupBCorrectWeightTypeIsSelectedWhenSubmitValidationDidNotPass
     */
    public function testGroupBCorrectWeightTypeIsSelectedWhenSubmitValidationDidNotPass(
        $vehicleWeightType,
        $vehicleWeight,
        $expectedSelectedVehicleWeightType
    )
    {
        //for testing which Weight Source is selected on list we use the real OfficalWeightSource classifier
        //it makes those classes coupled but we can check that when logic of this class changes, wrong value will be
        //selected in action
        $this->withRealOfficialWeightSourceForVehicle();

        $this->mockMethods();

        $action = $this->buildAction();

        $previousData['vehicleWeight'] = $vehicleWeight;
        $previousData['weightType'] = $vehicleWeightType;
        $action->setPreviousActionResult(new ViewActionResult(), $previousData);

        $actionResult = $action->execute(1);

        $actualPreselectValue = $actionResult->getViewModel()->getVariable('selectedWeightType');
        $this->assertEquals($expectedSelectedVehicleWeightType, $actualPreselectValue);
    }

    //logic of this preselection is not different between classes, we test only for default class (4)
    public function dataProviderTestGroupBCorrectWeightTypeIsSelectedWhenSubmitValidationDidNotPass()
    {
        return [
            //invalid weight, but this is after submit validation fails, so we select whatever was picked
            [WeightSourceCode::VSI, null, WeightSourceCode::VSI],
            [WeightSourceCode::VSI, "", WeightSourceCode::VSI],
            [WeightSourceCode::VSI, "blablabla", WeightSourceCode::VSI],
            //check every possible Weight Type is retained
            [WeightSourceCode::MISW, "", WeightSourceCode::VSI],
            [WeightSourceCode::ORD_MISW, "", WeightSourceCode::VSI],
            [WeightSourceCode::PRESENTED, "", WeightSourceCode::PRESENTED],
            [WeightSourceCode::NOT_APPLICABLE, "", WeightSourceCode::NOT_APPLICABLE],
            //valid weight
            [WeightSourceCode::VSI, "1234", WeightSourceCode::VSI],
        ];
    }

    /**
     * @dataProvider vehicleClassGroupADP
     * @param $vehicleClass
     */
    public function testGroupAIfNoBrakeTestResultsThenPopulateBrakeTestTypesInDtoWithSiteDefaults($vehicleClass)
    {
        $this->withoutBrakeTestResult();
        $this->withSite();
        $this->mockMethods($vehicleClass);

        $this->mockRestClient
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(
                [
                    'data' => $this->createVtsDto()
                ]
            ));

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        $viewModel = $actionResult->getViewModel();

        $this->assertEquals(
            $viewModel->brakeTestType,
            self::DEFAULT_SERVICE_BRAKE_TEST_TYPE_CODE
        );
    }

    /**
     * @dataProvider vehicleClassGroupBDP
     * @param $vehicleClass
     */
    public function testGroupBIfNoBrakeTestResultsThenPopulateBrakeTestTypesInDtoWithSiteDefaults($vehicleClass)
    {
        $this->withoutBrakeTestResult();
        $this->withSite();
        $this->mockMethods($vehicleClass);

        $this->mockRestClient
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(
                [
                    'data' => $this->createVtsDto()
                ]
            ));

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        /** @var BrakeTestConfigurationClass3AndAboveHelper $configHelper */
        $configHelper = $actionResult->getViewModel()->configHelper;

        $this->assertEquals(
            $configHelper->getServiceBrakeTestType(),
            self::DEFAULT_SERVICE_BRAKE_TEST_TYPE_CODE
        );

        $this->assertEquals(
            $configHelper->getParkingBrakeTestType(),
            self::DEFAULT_PARKING_BRAKE_TEST_TYPE_CODE
        );
    }

    /**
     * @dataProvider vehicleClassGroupADP
     * @param $vehicleClass
     */
    public function testGroupAIfNoBrakeTestResultsThenPopulateBrakeTestTypesInDtoWithMapperDefaults($vehicleClass)
    {
        $this->withoutBrakeTestResult();
        $this->withSite();
        $this->mockMethods($vehicleClass);

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        // @see BrakeTestConfigurationClass1And2Mapper::mapToDefaultDto

        $viewModel = $actionResult->getViewModel();

        $this->assertEquals(BrakeTestTypeCode::ROLLER, $viewModel->brakeTestType);
        $this->assertEquals('', $viewModel->vehicleWeightFront);
        $this->assertEquals('', $viewModel->vehicleWeightRear);
        $this->assertEquals('', $viewModel->riderWeight);
        $this->assertEquals('', $viewModel->sidecarWeight);
    }

    public function vehicleClassGroupADP()
    {
        return [
            [1],
            [2]
        ];
    }

    public function vehicleClassGroupBDP()
    {
        return [
            [3],
            [4],
            [5],
            [7],
        ];
    }

    private function withoutBrakeTestResult()
    {
        $this->motTestData->brakeTestResult = null;

        return $this;
    }

    private function withSite()
    {
        $this->motTestData->site = new stdClass();
        $this->motTestData->site->id = 5;
        $this->motTestData->site->number = 555;
        $this->motTestData->site->name = 'Site 1';
        $this->motTestData->site->address = [];

        return $this->motTestData->site;
    }

    /**
     * @param string $vehicleClassCode
     */
    private function mockMethods($vehicleClassCode = '4')
    {
        $this->mockVehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleByIdAndVersion')
            ->willReturn($this->mockDvsaVehicle);

        $vehicleClassMock = XMock::of(VehicleClass::class);

        $vehicleClassMock
            ->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn($vehicleClassCode);

        $this->mockDvsaVehicle
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $this->mockCatalogService
            ->method('getBrakeTestTypes')
            ->willReturn([]);
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    private function withMotTestStatus($status)
    {
        $this->motTestData->status = $status;

        return $this;
    }

    /**
     * @param int $vehicleWeight
     * @param int $previousTestVehicleWeight
     * @param string $serviceBrake1TestType
     * @param string $parkingBrakeTestType
     *
     * @return $this
     */
    private function withBrakeTestResults(
        $vehicleWeight,
        $previousTestVehicleWeight,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    )
    {
        $this->motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $this->motTestData->brakeTestResult->previousTestVehicleWeight = $previousTestVehicleWeight;
        $this->motTestData->brakeTestResult->serviceBrake1TestType = $serviceBrake1TestType;
        $this->motTestData->brakeTestResult->parkingBrakeTestType = $parkingBrakeTestType;

        return $this;
    }

    private function withBrakeTestWeightType(string $weightType)
    {
        $this->motTestData->brakeTestResult->weightType = $weightType;
    }

    private function withEmptyBrakeTestResult()
    {
        $this->motTestData->brakeTestResult = null;
    }

    /**
     * @return ViewBrakeTestConfigurationAction
     */
    private function buildAction()
    {
        $this->mockMotTestService
            ->expects($this->any())
            ->method('getMotTestByTestNumber')
            ->willReturn(new MotTest($this->motTestData));

        $action = new ViewBrakeTestConfigurationAction(
            XMock::of(WebPerformMotTestAssertion::class),
            XMock::of(ContingencySessionManager::class),
            $this->mockCatalogService,
            $this->mockRestClient,
            XMock::of(BrakeTestConfigurationContainerHelper::class),
            $this->mockVehicleService,
            $this->mockMotTestService,
            XMock::of(BrakeTestConfigurationService::class),
            $this->brakeTestConfigurationClass3AndAboveMapper
        );

        return $action;
    }

    /**
     * @param $returnValue
     * @param int $invocationCount
     */
    private function withOfficialWeightSourceSpec($returnValue, $invocationCount = 1)
    {
        $this->officialWeightSourceForVehicle
            ->expects($this->convertInvocationCount($invocationCount))
            ->method('isSatisfiedBy')
            ->willReturn($returnValue);
    }

    /**
     * @param $count
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
     */
    private function convertInvocationCount($count)
    {
        switch((int)$count){
            case 0:
                return $this->never();
            case 1:
                return $this->once();
            case 2:
                return $this->exactly(2);
            default:
                return $this->any();
        }
    }

    public function featureToggleAndSpecificationWontBeCalledDP()
    {
        return [
            // ftValue, ftIC, specValue, specIC
            [true, 0],
            [false, 0],
            [false, 0],
            [false, 0],
        ];
    }

    /**
     * @param string $serviceBrakeTestType
     * @param string $serviceBrakeTestTypeCode
     * @param string $parkingBrakeTestType
     * @param string $parkingBrakeTestTypeCode
     * @return VehicleTestingStationDto
     */
    private function createVtsDto(
        $serviceBrakeTestType = self::DEFAULT_SERVICE_BRAKE_TEST_TYPE,
        $serviceBrakeTestTypeCode = self::DEFAULT_SERVICE_BRAKE_TEST_TYPE_CODE,
        $parkingBrakeTestType = self::DEFAULT_PARKING_BRAKE_TEST_TYPE,
        $parkingBrakeTestTypeCode = self::DEFAULT_PARKING_BRAKE_TEST_TYPE_CODE
    )
    {
        $dto = (new VehicleTestingStationDto())
            ->setDefaultBrakeTestClass1And2(
                $this->createBrakeTestTypeDto($serviceBrakeTestType, $serviceBrakeTestTypeCode)
            )
            ->setDefaultServiceBrakeTestClass3AndAbove(
                $this->createBrakeTestTypeDto($serviceBrakeTestType, $serviceBrakeTestTypeCode)
            )
            ->setDefaultParkingBrakeTestClass3AndAbove(
                $this->createBrakeTestTypeDto($parkingBrakeTestType, $parkingBrakeTestTypeCode)
            );

        return $dto;
    }

    /**
     * @param $serviceBrakeTestType
     * @param $serviceBrakeTestTypeCode
     * @return BrakeTestTypeDto
     */
    private function createBrakeTestTypeDto($serviceBrakeTestType, $serviceBrakeTestTypeCode)
    {
        return (new BrakeTestTypeDto())
            ->setName($serviceBrakeTestType)
            ->setCode($serviceBrakeTestTypeCode);
    }

    private function withBrakeTestServiceBrake1TestType($brakeTestTypeCode)
    {
        $this->motTestData->brakeTestResult->serviceBrake1TestType = $brakeTestTypeCode;
    }

    private function withBrakeTestParkinbgBrakeTestType($brakeTestTypeCode)
    {
        $this->motTestData->brakeTestResult->parkingBrakeTestType = $brakeTestTypeCode;
    }

    private function withVehicleWeight($vehicleWeight)
    {
        $this->mockDvsaVehicle->expects($this->any())
            ->method("getWeight")
            ->willReturn($vehicleWeight);
    }

    private function withVehicleWeightType($weightSourceCode)
    {
        $data = new stdClass();
        $data->code = $weightSourceCode;
        $weightSource = new WeightSource($data);

        $this->mockDvsaVehicle->expects($this->any())
            ->method("getWeightSource")
            ->willReturn($weightSource);
    }

    private function withRealOfficialWeightSourceForVehicle()
    {
        $this->officialWeightSourceForVehicle = new OfficialWeightSourceForVehicle();

        $this->brakeTestConfigurationClass3AndAboveMapper = new BrakeTestConfigurationClass3AndAboveMapper(
            $this->officialWeightSourceForVehicle
        );
    }
}
