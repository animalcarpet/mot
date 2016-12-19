<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\BrakeTestResultClass12Test;
use DvsaEntitiesTest\Entity\BrakeTestResultClass3AndAboveTest;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\Validator\MotTestValidator;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class BrakeTestResultServiceTest
 */
class BrakeTestResultServiceTest extends AbstractServiceTestCase
{
    const MOCK_ENTITY_MANAGER = 'mockEntityManager';
    const MOCK_VALIDATOR = 'mockBrakeTestResultValidator';
    const MOCK_CONFIGURATION_VALIDATOR = 'mockBrakeTestConfigurationValidator';
    const MOCK_HYDRATOR = 'mockHydrator';
    const MOCK_CALCULATOR_CLASS_1_2 = 'mockBrakeTestResultClass12Calculator';
    const MOCK_CALCULATOR_CLASS_ABOVE_3 = 'mockBrakeTestResultClassAbove3Calculator';
    const MOCK_MAPPER_CLASS_ABOVE_3 = 'mockBrakeTestResultClassAbove3Mapper';
    const MOCK_MAPPER_CLASS_1_2 = 'mockBrakeTestResultClass12Mapper';
    const MOCK_MOT_TEST_VALIDATOR = 'mockMotTestValidator';
    const MOCK_REASON_FOR_REJECTION = 'mockReasonForRejection';
    const MOCK_PERFORM_MOT_TEST_ASSERTION = 'mockPerformMotTestAssertion';

    const TYPE_TEST_CLASS_1_2 = true;
    const TYPE_TEST_CLASS_ABOVE_3 = false;

    const FAILURE_RFR_BOTH_UNDER_SECONDARY_MIN = 1;
    const FAILURE_RFR_ONE_NOT_REACHING_PRIMARY_MIN = 2;
    const FAILURE_RFR_BOTH_UNDER_PRIMARY_MIN = 3;


    public function testBrakeTestOfAbandonedMotTestThrowsException()
    {
        $this->runBrakeTestWithUnsuitableMotTestStatus(MotTestStatusName::ABANDONED);
    }

    public function testBrakeTestOfInactiveMotTestThrowsException()
    {
        $this->runBrakeTestWithUnsuitableMotTestStatus(MotTestStatusName::FAILED);
    }

    private function runBrakeTestWithUnsuitableMotTestStatus($status) {
        $mocks = $this->getMocksForBrakeTestResultService();

        $motTest = $this->getTestMotTest();
        $motTest->getMotTestStatus()->setName($status);

        $data = BrakeTestResultClass3AndAboveTest::getTestData();
        $brakeTestResultPrototype = new BrakeTestResultClass3AndAbove();
        $brakeTestResult = $this->getTestBrakeTestResultClassAbove3WithEntities();
        $brakeTestResult
            ->setServiceBrake1TestType(BrakeTestTypeFactory::roller())
            ->setParkingBrakeTestType(BrakeTestTypeFactory::roller())
            ->setServiceBrake1Efficiency(50)
            ->setParkingBrakeEfficiencyPass(false)
            ->setServiceBrake1EfficiencyPass(false)
            ->getServiceBrake1Data()->setImbalancePass(false);

        $brakeTestResult->getServiceBrake1Data()->setImbalancePassForAxle(1, false);
        $brakeTestResult->getServiceBrake1Data()->setImbalancePassForAxle(2, true);
        $brakeTestResult->getServiceBrake1Data()->setImbalancePassForAxle(3, null);

        $brakeTestResultService = $this->constructBrakeTestResultServiceWithMocks($mocks);

        $this->setExpectedException(BadRequestException::class, InvalidTestStatus::getMessage($status));
        $brakeTestResultService->createBrakeTestResult($motTest, $data);
    }

    public function testCreateBrakeTestResultOk()
    {
        $mocks = $this->getMocksForBrakeTestResultService();

        $motTest = $this->getTestMotTest();
        $data = BrakeTestResultClass3AndAboveTest::getTestData();
        $brakeTestResultPrototype = new BrakeTestResultClass3AndAbove();
        $brakeTestResult = $this->getTestBrakeTestResultClassAbove3WithEntities();
        $brakeTestResult
            ->setServiceBrake1TestType(BrakeTestTypeFactory::roller())
            ->setParkingBrakeTestType(BrakeTestTypeFactory::roller())
            ->setServiceBrake1Efficiency(50)
            ->setParkingBrakeEfficiencyPass(false)
            ->setServiceBrake1EfficiencyPass(false)
            ->getServiceBrake1Data()->setImbalancePass(false);

        $brakeTestResult->getServiceBrake1Data()->setImbalancePassForAxle(1, false);
        $brakeTestResult->getServiceBrake1Data()->setImbalancePassForAxle(2, true);
        $brakeTestResult->getServiceBrake1Data()->setImbalancePassForAxle(3, null);

        $this->setupMockForSingleCall(
            $mocks[self::MOCK_MAPPER_CLASS_ABOVE_3],
            'mapToObject',
            $brakeTestResultPrototype,
            $data
        );
        $this->setupMockForSingleCall(
            $mocks[self::MOCK_VALIDATOR],
            'validateBrakeTestResultClass3AndAbove',
            true,
            $brakeTestResultPrototype
        );
        $this->setupMockForSingleCall(
            $mocks[self::MOCK_CALCULATOR_CLASS_ABOVE_3],
            'calculateBrakeTestResult',
            $brakeTestResult,
            $brakeTestResultPrototype
        );

        $brakeTestResultService = $this->constructBrakeTestResultServiceWithMocks($mocks);

        $resultBrakeTestResult = $brakeTestResultService->createBrakeTestResult($motTest, $data);

        $this->assertInstanceOf(BrakeTestResultSubmissionSummary::class, $resultBrakeTestResult);
        $this->assertEquals($brakeTestResult, $resultBrakeTestResult->brakeTestResultClass3AndAbove);
        $this->assertEquals($this->getRfrExpectedClass4Result(), $resultBrakeTestResult->reasonsForRejectionList);
    }

    public function testCreateBrakeTestResultClass1AndNoRfrs()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();
        $brakeTestResult->setGeneralPass(true);
        $result = $this->checkCreateBrakeTestResultClasses1And2($brakeTestResult);
        $this->assertEmpty($result->reasonsForRejectionList);
    }

    public function testCreateBrakeTestResultClass1AndRfrBothUnder25()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();
        $brakeTestResult->setGeneralPass(false);
        $result = $this->checkCreateBrakeTestResultClasses1And2(
            $brakeTestResult, self::FAILURE_RFR_BOTH_UNDER_SECONDARY_MIN
        );
        $this->assertEquals($this->getOneExpectedRfr('491'), $result->reasonsForRejectionList);
    }

    public function testCreateBrakeTestResultClass1AndRfrBothUnder30()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();
        $brakeTestResult->setGeneralPass(false);
        $result = $this->checkCreateBrakeTestResultClasses1And2(
            $brakeTestResult, self::FAILURE_RFR_BOTH_UNDER_PRIMARY_MIN
        );
        $this->assertEquals($this->getOneExpectedRfr('489'), $result->reasonsForRejectionList);
    }

    public function testCreateBrakeTestResultClass1AndRfrOneUnder25()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();
        $brakeTestResult->setGeneralPass(false);
        $result = $this->checkCreateBrakeTestResultClasses1And2(
            $brakeTestResult, self::FAILURE_RFR_ONE_NOT_REACHING_PRIMARY_MIN
        );
        $this->assertEquals($this->getOneExpectedRfr('490'), $result->reasonsForRejectionList);
    }

    private function checkCreateBrakeTestResultClasses1And2($brakeTestResult, $rfrFault = 0)
    {
        $mocks = $this->getMocksForBrakeTestResultService();

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_1));

        $motTest = $this->getTestMotTest();
        $motTest->getVehicle()->setModelDetail($modelDetail);
        $data = BrakeTestResultClass12Test::getTestBrakeTestResultData();
        $brakeTestResultPrototype = new BrakeTestResultClass12();
        $this->setupMockForSingleCall(
            $mocks[self::MOCK_MAPPER_CLASS_1_2],
            'mapToObject',
            $brakeTestResultPrototype,
            $data
        );
        $this->setupMockForSingleCall(
            $mocks[self::MOCK_VALIDATOR],
            'validateBrakeTestResultClass1And2',
            true,
            $brakeTestResultPrototype
        );
        $calculatorMockHandler = new MockHandler($mocks[self::MOCK_CALCULATOR_CLASS_1_2], $this);
        $calculatorMockHandler->next('calculateBrakeTestResult')
            ->with($brakeTestResultPrototype)
            ->will($this->returnValue($brakeTestResult));
        $calculatorMockHandler->next('areBothControlsUnderSecondaryMinimum')
            ->with($brakeTestResult)
            ->will($this->returnValue(!!($rfrFault == self::FAILURE_RFR_BOTH_UNDER_SECONDARY_MIN)));
        if ($rfrFault != self::FAILURE_RFR_BOTH_UNDER_SECONDARY_MIN) {
            $calculatorMockHandler->next('noControlReachesPrimaryMinimum')
                ->with($brakeTestResult)
                ->will($this->returnValue(!!($rfrFault == self::FAILURE_RFR_BOTH_UNDER_PRIMARY_MIN)));
            if ($rfrFault != self::FAILURE_RFR_BOTH_UNDER_PRIMARY_MIN) {
                $calculatorMockHandler->next('oneControlNotReachingSecondaryMinimum')
                    ->with($brakeTestResult)
                    ->will($this->returnValue(!!($rfrFault == self::FAILURE_RFR_ONE_NOT_REACHING_PRIMARY_MIN)));
            }
        }

        $brakeTestResultService = $this->constructBrakeTestResultServiceWithMocks($mocks);
        $resultBrakeTestResult = $brakeTestResultService->createBrakeTestResult($motTest, $data);
        $this->assertInstanceOf(BrakeTestResultSubmissionSummary::class, $resultBrakeTestResult);
        $this->assertEquals($brakeTestResult, $resultBrakeTestResult->brakeTestResultClass1And2);

        return $resultBrakeTestResult;
    }

    public function testUpdateNewBrakeTestResult()
    {
        $motTestId = 1;

        $motTest = MotTestServiceTest::getTestMotTestEntity();
        $motTest->setId($motTestId);

        $motTest->setBrakeTestResultClass3AndAbove(null);
        $reasonForRejection = new ReasonForRejection();
        $reasonForRejection->setTestItemSelector(new TestItemSelector());
        $brakeTestResultData = BrakeTestResultClass3AndAboveTest::getTestData();
        $testBrakeTestResult = BrakeTestResultClass3AndAboveTest::getTestBrakeTestResult();
        $testMotRfr = new MotTestReasonForRejection();
        $testMotRfr->setMotTest($motTest);

        $mocks = $this->getMocksForBrakeTestResultService();

        $createBrakeTestResult = new BrakeTestResultSubmissionSummary();
        $createBrakeTestResult->brakeTestResultClass3AndAbove = $testBrakeTestResult;
        $createBrakeTestResult->addReasonForRejection(1, 2, 3, 'what');

        $mocks[self::MOCK_REASON_FOR_REJECTION]
            ->expects($this->once())
            ->method('createRfrFromData')
            ->willReturn(
                (new MotTestReasonForRejection())
                    ->setMotTest($motTest)
                    ->setGenerated(true)
            );

        $mockRfrRepository = $this->getMockRepository();
        $mockRfrRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['generated' => true, 'motTestId' => $motTestId])
            ->will($this->returnValue(array($testMotRfr)));

        $mocks['mockEntityManager'] = $this->getMockEntityManager();

        $mockEntityManager = $mocks['mockEntityManager'];
        $this->setupMockForSpecificCalls(
            $mockEntityManager,
            [
                [
                    self::AT     => 0,
                    self::METHOD => 'getRepository',
                    self::WITH   => MotTestReasonForRejection::class,
                    self::WILL   => $this->returnValue($mockRfrRepository),
                ],
                [
                    self::AT     => 1,
                    self::METHOD => 'persist',
                    self::WITH   =>
                        $this->logicalAnd(
                            $this->isInstanceOf(\DvsaEntities\Entity\MotTestReasonForRejection::class),
                            $this->attributeEqualTo('motTest', $motTest),
                            $this->attributeEqualTo('generated', true)
                        ),
                    self::WILL   => $this->returnValue(null),
                ],
                [
                    self::AT     => 2,
                    self::METHOD => 'persist',
                    self::WITH   => $this->isInstanceOf(\DvsaEntities\Entity\MotTest::class),
                    self::WILL   => $this->returnValue(null),
                ],
                [
                    self::AT     => 3,
                    self::METHOD => 'flush',
                    self::WITH   => null,
                    self::WILL   => $this->returnValue(null),
                ],
            ]
        );

        /** @var BrakeTestResultService|PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockBuilder(BrakeTestResultService::class)
            ->setConstructorArgs(
                [
                    $mocks[self::MOCK_ENTITY_MANAGER],
                    $mocks[self::MOCK_VALIDATOR],
                    $mocks[self::MOCK_CONFIGURATION_VALIDATOR],
                    $mocks[self::MOCK_HYDRATOR],
                    $mocks[self::MOCK_CALCULATOR_CLASS_ABOVE_3],
                    $mocks[self::MOCK_CALCULATOR_CLASS_1_2],
                    $mocks[self::MOCK_MAPPER_CLASS_ABOVE_3],
                    $mocks[self::MOCK_MAPPER_CLASS_1_2],
                    $this->getMockAuthorizationService(),
                    $mocks[self::MOCK_MOT_TEST_VALIDATOR],
                    $mocks[self::MOCK_REASON_FOR_REJECTION],
                    $mocks[self::MOCK_PERFORM_MOT_TEST_ASSERTION]
                ]
            )
            ->setMethods(['createBrakeTestResult'])
            ->getMock();

        $mock->expects($this->once())
            ->method('createBrakeTestResult')
            ->with($motTest, $brakeTestResultData)
            ->will($this->returnValue($createBrakeTestResult));

        $this->assertTrue($mock->updateBrakeTestResult($motTest, $brakeTestResultData));
    }

    public function testExtractBrakeTestResultClassAbove3()
    {
        $this->checkExtract(self::TYPE_TEST_CLASS_ABOVE_3);
    }

    public function testExtractBrakeTestResultClass1And2()
    {
        $this->checkExtract(self::TYPE_TEST_CLASS_1_2);
    }

    public function checkExtract($class1And2)
    {
        $brakeTestResults = $class1And2 ? new BrakeTestResultClass12() : new BrakeTestResultClass3AndAbove();
        $mocks = $this->getMocksForBrakeTestResultService();
        $mockCalculatorHandler = new MockHandler(
            $mocks[$class1And2 ? self::MOCK_CALCULATOR_CLASS_1_2 : self::MOCK_CALCULATOR_CLASS_ABOVE_3],
            $this
        );
        if ($class1And2) {
            $brakeTestResults->setBrakeTestType(BrakeTestTypeFactory::roller());

            $calculatorMockParams = [
                'calculateControl1PercentLocked' => 'calculatedControl1',
                'calculateControl2PercentLocked' => 'calculatedControl2',
            ];
            $expectedResult = [
                'control1LockPercent' => 'calculatedControl1',
                'control2LockPercent' => 'calculatedControl2',
                'brakeTestType'       => BrakeTestTypeCode::ROLLER,
            ];
            foreach ($calculatorMockParams as $method => $returnValue) {
                $mockCalculatorHandler
                    ->next($method)
                    ->with($brakeTestResults)
                    ->will($this->returnValue($returnValue));
            }
        } else {
            $brakeTestResults->setMotTest($this->getTestMotTest());
            $brakeTestResults->setServiceBrake1TestType(BrakeTestTypeFactory::roller());
            $brakeTestResults->setServiceBrake2TestType(BrakeTestTypeFactory::roller());
            $brakeTestResults->setParkingBrakeTestType(BrakeTestTypeFactory::roller());
            $serviceBrake1 = new BrakeTestResultServiceBrakeData();
            $brakeTestResults->setServiceBrake1Data($serviceBrake1);
            $serviceBrake2 = new BrakeTestResultServiceBrakeData();
            $brakeTestResults->setServiceBrake2Data($serviceBrake2);
            $mockCalculatorHandler
                ->next('calculateParkingBrakePercentLocked')
                ->with($brakeTestResults)
                ->will($this->returnValue('calculatedParkingBrake'));
            $mockCalculatorHandler
                ->next('calculateServiceBrakePercentLocked')
                ->with($serviceBrake1)
                ->will($this->returnValue('calculatedService1'));
            $mockCalculatorHandler
                ->next('calculateServiceBrakePercentLocked')
                ->with($serviceBrake2)
                ->will($this->returnValue('calculatedService2'));
            $expectedResult = [
                'parkingBrakeLockPercent' => 'calculatedParkingBrake',
                'serviceBrake1Data'       => ['lockPercent' => 'calculatedService1'],
                'serviceBrake2Data'       => ['lockPercent' => 'calculatedService2'],
                'serviceBrake1TestType'   => BrakeTestTypeCode::ROLLER,
                'serviceBrake2TestType'   => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTestType'    => BrakeTestTypeCode::ROLLER,
                'weightType'              => null,
            ];
        }
        $mockHydratorHandler = new MockHandler($mocks[self::MOCK_HYDRATOR], $this);
        $mockHydratorHandler
            ->next('extract')
            ->with($brakeTestResults)
            ->will($this->returnValue([]));

        $brakeTestResultService = $this->constructBrakeTestResultServiceWithMocks($mocks);
        $resultExtract = $brakeTestResultService->extract($brakeTestResults);
        $this->assertEquals($expectedResult, $resultExtract);
    }

    private function getMocksForBrakeTestResultService()
    {
        $mockBrakeTestResultValidator = $this->getMockWithDisabledConstructor(
            \DvsaMotApi\Service\Validator\BrakeTestResultValidator::class
        );
        $mockBrakeTestConfigurationValidator = $this->getMockWithDisabledConstructor(
            \DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator::class
        );
        $mockHydrator = $this->getMockHydrator();
        $mockEntityManager = $this->getMockEntityManager();
        $mockBrakeTestResultCalculator = $this->getMockWithDisabledConstructor(
            \DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator::class
        );
        $mockBrakeTestResultClass12Calculator = $this->getMockWithDisabledConstructor(
            \DvsaMotApi\Service\Calculator\BrakeTestResultClass1And2Calculator::class
        );
        $mockBrakeTestResultClass3AndAboveMapper = $this->getMockWithDisabledConstructor(
            \DvsaMotApi\Mapper\BrakeTestResultClass3AndAboveMapper::class
        );
        $mockBrakeTestResultClass12Mapper = $this->getMockWithDisabledConstructor(
            \DvsaMotApi\Mapper\BrakeTestResultClass12Mapper::class
        );

        $mockTestValidator = $this->getMockWithDisabledConstructor(MotTestValidator::class);

        $mockReasonForRejectionSrvc = XMock::of(MotTestReasonForRejectionService::class);

        $mockPerformMotTestAssertion = $this->getMockWithDisabledConstructor(
            ApiPerformMotTestAssertion::class
        );

        return [
            self::MOCK_CALCULATOR_CLASS_ABOVE_3 => $mockBrakeTestResultCalculator,
            self::MOCK_CALCULATOR_CLASS_1_2     => $mockBrakeTestResultClass12Calculator,
            self::MOCK_HYDRATOR                 => $mockHydrator,
            self::MOCK_VALIDATOR                => $mockBrakeTestResultValidator,
            self::MOCK_CONFIGURATION_VALIDATOR  => $mockBrakeTestConfigurationValidator,
            self::MOCK_ENTITY_MANAGER           => $mockEntityManager,
            self::MOCK_MAPPER_CLASS_ABOVE_3     => $mockBrakeTestResultClass3AndAboveMapper,
            self::MOCK_MAPPER_CLASS_1_2         => $mockBrakeTestResultClass12Mapper,
            self::MOCK_MOT_TEST_VALIDATOR       => $mockTestValidator,
            self::MOCK_REASON_FOR_REJECTION     => $mockReasonForRejectionSrvc,
            self::MOCK_PERFORM_MOT_TEST_ASSERTION => $mockPerformMotTestAssertion,
        ];
    }

    private function getRfrExpectedClass4Result()
    {
        return [
            [
                'rfrId'                => '8357',
                'type'                 => 'FAIL',
                'locationLongitudinal' => null,
                'comment'              => null
            ],
            [
                'rfrId'                => '8358',
                'type'                 => 'FAIL',
                'locationLongitudinal' => null,
                'comment'              => null
            ],
            [
                'rfrId'                => '8343',
                'type'                 => 'FAIL',
                'locationLongitudinal' => 'front',
                'comment'              => null
            ],
        ];
    }

    private function getOneExpectedRfr($rfrNumber)
    {
        return [
            [
                'rfrId'                => $rfrNumber,
                'type'                 => 'FAIL',
                'locationLongitudinal' => null,
                'comment'              => null
            ],
        ];
    }

    private function constructBrakeTestResultServiceWithMocks($mocks)
    {
        return new BrakeTestResultService(
            $mocks[self::MOCK_ENTITY_MANAGER],
            $mocks[self::MOCK_VALIDATOR],
            $mocks[self::MOCK_CONFIGURATION_VALIDATOR],
            $mocks[self::MOCK_HYDRATOR],
            $mocks[self::MOCK_CALCULATOR_CLASS_ABOVE_3],
            $mocks[self::MOCK_CALCULATOR_CLASS_1_2],
            $mocks[self::MOCK_MAPPER_CLASS_ABOVE_3],
            $mocks[self::MOCK_MAPPER_CLASS_1_2],
            $this->getMockAuthorizationService(),
            $mocks[self::MOCK_MOT_TEST_VALIDATOR],
            $mocks[self::MOCK_REASON_FOR_REJECTION],
            $mocks[self::MOCK_PERFORM_MOT_TEST_ASSERTION]
        );
    }

    private function getTestMotTest($class = Vehicle::VEHICLE_CLASS_4)
    {
        $vehicleClass = new VehicleClass($class);
        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass($vehicleClass);

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setFirstUsedDate(new \DateTime('2000-01-01'));
        $motTest = new MotTest();
        $motTest->setVehicle($vehicle);
        $motTestStatus = new MotTestStatus();
        $motTestStatus->setName(MotTestStatusName::ACTIVE);
        $motTest->setStatus($motTestStatus);
        $motTest->setVehicleClass($vehicleClass);
        
        return $motTest;
    }

    private function getTestBrakeTestResultClassAbove3WithEntities()
    {
        $serviceBrake1 = new BrakeTestResultServiceBrakeData();
        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult->setServiceBrake1Data($serviceBrake1);
        return $brakeTestResult;
    }
}
