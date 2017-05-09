<?php

namespace DvsaMotApiTest\Service\Validator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\Vehicle;
use DvsaEntitiesTest\Entity\BrakeTestResultClass12Test;
use DvsaEntitiesTest\Entity\BrakeTestResultClass3AndAboveTest;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;

/**
 * Class BrakeTestConfigurationValidatorTest.
 */
class BrakeTestConfigurationValidatorTest extends AbstractServiceTestCase
{
    private $brakeTestValidationRules
        = [
            VehicleClassCode::CLASS_3 => [
                BrakeTestTypeCode::ROLLER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::PLATE => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => true,
                    BrakeTestTypeCode::DECELEROMETER => false,
                    BrakeTestTypeCode::GRADIENT => false,
                ],
                BrakeTestTypeCode::DECELEROMETER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::GRADIENT => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => false,
                    BrakeTestTypeCode::GRADIENT => false,
                ],
            ],
            VehicleClassCode::CLASS_4 => [
                BrakeTestTypeCode::ROLLER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::PLATE => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => true,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::DECELEROMETER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::GRADIENT => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => false,
                    BrakeTestTypeCode::GRADIENT => false,
                ],
            ],
            VehicleClassCode::CLASS_5 => [
                BrakeTestTypeCode::ROLLER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::PLATE => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => false,
                    BrakeTestTypeCode::GRADIENT => false,
                ],
                BrakeTestTypeCode::DECELEROMETER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::GRADIENT => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => false,
                    BrakeTestTypeCode::GRADIENT => false,
                ],
            ],
            VehicleClassCode::CLASS_7 => [
                BrakeTestTypeCode::ROLLER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::PLATE => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => true,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::DECELEROMETER => [
                    BrakeTestTypeCode::ROLLER => true,
                    BrakeTestTypeCode::PLATE => true,
                    BrakeTestTypeCode::DECELEROMETER => true,
                    BrakeTestTypeCode::GRADIENT => true,
                ],
                BrakeTestTypeCode::GRADIENT => [
                    BrakeTestTypeCode::ROLLER => false,
                    BrakeTestTypeCode::PLATE => false,
                    BrakeTestTypeCode::DECELEROMETER => false,
                    BrakeTestTypeCode::GRADIENT => false,
                ],
            ],
        ];

    /**
     * @dataProvider brakeTestConfigurationValidatorClass3AndAbove
     */
    public function testBrakeTestConfigurationClass3AndAboveWithCallBack(
        $message,
        $brakeTestConfiguration,
        $vehicleClass,
        callable $callback = null
    ) {
        $brakeTestConfigurationValidator = new BrakeTestConfigurationValidator();
        try {
            $result = $brakeTestConfigurationValidator->validateBrakeTestConfigurationClass3AndAbove(
                $brakeTestConfiguration, $vehicleClass
            );
            $this->assertTrue($result, $message);
            if ($result) {
                $this->assertNull($callback, 'An exception was expected but none encountered');
            }
        } catch (BadRequestException $ex) {
            if (!$callback) {
                throw $ex;
            }
            $callback($ex);
        }
    }

    /**
     * @dataProvider brakeTestConfigurationValidatorClass12
     */
    public function testBrakeTestConfigurationClass12WithCallBack(
        $message,
        $brakeTestConfiguration,
        callable $callback = null
    ) {
        $brakeTestConfigurationValidator = new BrakeTestConfigurationValidator();
        try {
            $result = $brakeTestConfigurationValidator->validateBrakeTestConfigurationClass12($brakeTestConfiguration);
            $this->assertTrue($result, $message);
        } catch (BadRequestException $ex) {
            if (!$callback) {
                throw $ex;
            }
            $callback($ex);
        }
    }

    public function testValidateBrakeTestTypeClass1And2WithValidType()
    {
        $brakeTestConfigurationValidator = new BrakeTestConfigurationValidator();
        $type = BrakeTestTypeCode::ROLLER;

        $brakeTestConfigurationValidator->validateBrakeTestTypeClass1And2($type);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     */
    public function testValidateBrakeTestTypeClass1And2WithInvalidTypeThrowsException()
    {
        $brakeTestConfigurationValidator = new BrakeTestConfigurationValidator();
        $type = null;

        $brakeTestConfigurationValidator->validateBrakeTestTypeClass1And2($type);
    }

    public function testValidateBrakeTestTypeClass3AndAboveWithValidType()
    {
        $brakeTestConfigurationValidator = new BrakeTestConfigurationValidator();
        $type = BrakeTestTypeCode::ROLLER;

        $brakeTestConfigurationValidator->validateBrakeTestTypeClass3AndAbove($type);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     */
    public function testValidateBrakeTestTypeClass3AndAboveWithInvalidTypeThrowsException()
    {
        $brakeTestConfigurationValidator = new BrakeTestConfigurationValidator();
        $type = null;

        $brakeTestConfigurationValidator->validateBrakeTestTypeClass3AndAbove($type);
    }

    public function testValidateBrakeTestTypeClass3AndAboveWithInvalidWeightType()
    {
        $brakeTestConfiguration = self::getValidBrakeTestResultClass3AndAbove();
        $brakeTestConfiguration->setWeightType(null);

        try {
            (new BrakeTestConfigurationValidator())->validateBrakeTestConfigurationClass3AndAbove(
                $brakeTestConfiguration,
                VehicleClassCode::CLASS_3
            );
        } catch (BadRequestException $e) {
            $errors = $e->getErrors();
        }

        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertEquals(BadRequestException::ERROR_CODE_INVALID_DATA, $error['code']);
        $this->assertEquals(BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT_TYPE, $error['message']);
        $this->assertEquals(BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT_TYPE, $error['displayMessage']);
    }

    public function testValidateBrakeTestTypeClass3AndAboveWithInvalidWeight()
    {
        $brakeTestConfiguration = self::getValidBrakeTestResultClass3AndAbove();
        $brakeTestConfiguration->setVehicleWeight(null);

        try {
            (new BrakeTestConfigurationValidator())->validateBrakeTestConfigurationClass3AndAbove(
                $brakeTestConfiguration,
                VehicleClassCode::CLASS_3
            );
        } catch (BadRequestException $e) {
            $errors = $e->getErrors();
        }

        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertEquals(BadRequestException::ERROR_CODE_INVALID_DATA, $error['code']);
        $this->assertEquals(BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT, $error['message']);
        $this->assertEquals(BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT, $error['displayMessage']);
    }

    public function testValidateBrakeTestTypeClass3AndAboveWithInvalidWeightAndWeightType()
    {
        $brakeTestConfiguration = self::getValidBrakeTestResultClass3AndAbove();
        $brakeTestConfiguration->setWeightType(null);
        $brakeTestConfiguration->setVehicleWeight(null);

        try {
            (new BrakeTestConfigurationValidator())->validateBrakeTestConfigurationClass3AndAbove(
                $brakeTestConfiguration,
                VehicleClassCode::CLASS_3
            );
        } catch (BadRequestException $e) {
            $errors = $e->getErrors();
        }

        $this->assertCount(2, $errors);

        $weightTypeError = $errors[0];
        $weightError = $errors[1];

        $this->assertEquals(BadRequestException::ERROR_CODE_INVALID_DATA, $weightTypeError['code']);
        $this->assertEquals(
            BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT_TYPE,
            $weightTypeError['message']
        );
        $this->assertEquals(
            BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT_TYPE,
            $weightTypeError['displayMessage']
        );

        $this->assertEquals(BadRequestException::ERROR_CODE_INVALID_DATA, $weightError['code']);
        $this->assertEquals(
            BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT,
            $weightError['message']
        );
        $this->assertEquals(
            BrakeTestConfigurationValidator::MESSAGE_INVALID_VEHICLE_WEIGHT,
            $weightError['displayMessage']
        );
    }

    public function testValidateBrakeTestTypeClass3AndAboveIgnoringInvalidWeightBasedOnWeightType()
    {
        $brakeTestConfiguration = self::getValidBrakeTestResultClass3AndAbove();
        $brakeTestConfiguration->getWeightType()->setCode(WeightSourceCode::NOT_APPLICABLE);
        $brakeTestConfiguration->setVehicleWeight(null);

        $result = (new BrakeTestConfigurationValidator())->validateBrakeTestConfigurationClass3AndAbove(
                $brakeTestConfiguration,
                VehicleClassCode::CLASS_3
            );

        $this->assertTrue($result);
    }

    public function brakeTestConfigurationValidatorClass12()
    {
        $callback = function ($ex) {
            self::hasErrors($ex, [BrakeTestConfigurationValidator::MESSAGE_CONTROL1_INVALID_BRAKE_TEST_TYPE]);
        };

        return
            [
                [
                    'Test valid brake test type roller for class 1,2 does not throw exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::roller()),
                ],
                [
                    'Test valid brake test type plate for class 1,2 does not throw exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::plate()),
                ],
                [
                    'Test valid brake test type decelerometer for class 1,2 does not throw exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::decelerometer()),
                ],
                [
                    'Test valid brake test type gradient for class 1,2 does not throw exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::gradient()),
                ],
                [
                    'Test valid brake test type floor for class 1,2 does not throw exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::floor()),
                ],
                [
                    'Test invalid brake test type throws exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::type('Invalid')),
                    $callback,
                ],
                [
                    'Test null brake test type throws exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::type(null)),
                    $callback,
                ],
                [
                    'Test empty value brake test type throws exception',
                    self::getBrakeTestConfigurationClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::type('')),
                    $callback,
                ],
            ];
    }

    public function brakeTestConfigurationValidatorClass3AndAbove()
    {
        $resultTable = null;
        //TEST COMBINATIONS
        $brakeTestValidationRules = $this->brakeTestValidationRules;
        foreach ($brakeTestValidationRules as $vehicleClass => $validationMatix) {
            foreach ($validationMatix as $serviceBrakeTestType => $serviceBrakeMatrix) {
                foreach ($serviceBrakeMatrix as $parkingBrakeTestType => $validationResult) {
                    if ($validationResult) {
                        $message
                            = 'Test that valid brake test type combination %s and %s for a class %d vehicle does not throw exception';
                        $formattedMessage = sprintf(
                            $message,
                            $serviceBrakeTestType,
                            $parkingBrakeTestType,
                            $vehicleClass
                        );
                        $callback = null;
                    } else {
                        $message
                            = 'Test that invalid brake test type combination %s and %s for a class %d vehicle throws exception';
                        $formattedMessage = sprintf(
                            $message,
                            $serviceBrakeTestType,
                            $parkingBrakeTestType,
                            $vehicleClass
                        );
                        $callback = function ($ex) {
                            self::hasErrors(
                                $ex,
                                [BrakeTestConfigurationValidator::MESSAGE_INVALID_COMBINATION_OF_BRAKE_TEST_TYPES]
                            );
                        };
                    }
                    $brakeTestConfiguration = self::getValidBrakeTestResultClass3AndAbove()
                        ->setServiceBrake1TestType(BrakeTestTypeFactory::type($serviceBrakeTestType))
                        ->setParkingBrakeTestType(BrakeTestTypeFactory::type($parkingBrakeTestType));
                    $resultTable[] = [$formattedMessage, $brakeTestConfiguration, $vehicleClass, $callback];
                }
            }
        }
        //TEST IF TYPE IS SAME FOR BOTH SERVICE BRAKES
        $brakeTestConfiguration = self::getValidBrakeTestResultClass3AndAbove()
            ->setServiceBrake1TestType(BrakeTestTypeFactory::roller())
            ->setServiceBrake2TestType(BrakeTestTypeFactory::plate());
        $diffTypesCallback = function ($ex) {
            self::hasErrors($ex, [BrakeTestConfigurationValidator::MESSAGE_DIFFERENT_SERVICE_BRAKE_TYPES]);
        };
        $resultTable[] = [
            'Test that service brake 1 and 2 has same type.',
            $brakeTestConfiguration,
            Vehicle::VEHICLE_CLASS_4,
            $diffTypesCallback, ];

        return $resultTable;
    }

    private static function getBrakeTestConfigurationClass1And2()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();

        return $brakeTestResult;
    }

    /**
     * @return \DvsaEntities\Entity\BrakeTestResultClass3AndAbove
     */
    private static function getValidBrakeTestResultClass3AndAbove()
    {
        $brakeTestResult = BrakeTestResultClass3AndAboveTest::getTestBrakeTestResult();

        return $brakeTestResult;
    }

    private function hasErrors(ServiceException $exception, array $expectedErrors)
    {
        $foundErrors = [];
        foreach ($exception->getErrors() as $foundError) {
            $foundErrors[] = $foundError['message'];
        }
        $this->assertEquals(count(array_intersect_key($foundErrors, $expectedErrors)), count($expectedErrors));
    }
}
