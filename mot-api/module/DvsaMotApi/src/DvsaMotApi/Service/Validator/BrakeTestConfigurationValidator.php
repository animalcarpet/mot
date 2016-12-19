<?php

namespace DvsaMotApi\Service\Validator;

use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;

/**
 * Class BrakeTestConfigurationValidator
 */
class BrakeTestConfigurationValidator extends AbstractValidator
{
    const MESSAGE_SERVICE_INVALID_BRAKE_TEST_TYPE = 'Invalid service brake test type';
    const MESSAGE_PARKING_INVALID_BRAKE_TEST_TYPE = 'Invalid parking brake test type';
    const MESSAGE_INVALID_COMBINATION_OF_BRAKE_TEST_TYPES = 'Invalid combination of brake test types';
    const MESSAGE_CONTROL1_INVALID_BRAKE_TEST_TYPE = 'Invalid brake test type on control one';
    const MESSAGE_DIFFERENT_SERVICE_BRAKE_TYPES = 'Service brake types must be the same';
    const MESSAGE_INVALID_VEHICLE_WEIGHT = 'Please enter a valid vehicle weight';
    const MESSAGE_INVALID_VEHICLE_WEIGHT_TYPE = 'Please choose vehicle weight type';
    const MESSAGE_INVALID_RIDER_WEIGHT = 'Please enter a valid rider weight';
    const MESSAGE_INVALID_SIDECAR_WEIGHT = 'Please enter a valid sidecar weight';

    private $validBrakeTestTypesClass3AndAbove
        = [
            BrakeTestTypeCode::DECELEROMETER,
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
            BrakeTestTypeCode::GRADIENT
        ];

    private $validBrakeTestTypesClass12
        = [
            BrakeTestTypeCode::DECELEROMETER,
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
            BrakeTestTypeCode::GRADIENT,
            BrakeTestTypeCode::FLOOR,
        ];

    public function validateBrakeTestConfigurationClass3AndAbove(
        BrakeTestResultClass3AndAbove $brakeTestResultClass3AndAbove,
        $vehicleClass
    ) {
        $validationException = $this->getEmptyBadRequestException();

        // weight applicable when at least test type includes efforts
        if ((
                $this->effortsApplicableForClass3AndAbove(
                    $brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode()
                )
                || $this->effortsApplicableForClass3AndAbove(
                    $brakeTestResultClass3AndAbove->getServiceBrake2TestType() ?
                        $brakeTestResultClass3AndAbove->getServiceBrake2TestType()->getCode() : null
                )
                || $this->effortsApplicableForClass3AndAbove(
                    $brakeTestResultClass3AndAbove->getParkingBrakeTestType()->getCode()
                ))
        ) {

            $this->processWeightAndWeightType($brakeTestResultClass3AndAbove, $validationException);

        }

        $this->validateBrakeTestTypesClass3AndAbove(
            $validationException,
            $brakeTestResultClass3AndAbove,
            $vehicleClass
        );

        if (count($validationException->getErrors())) {
            throw $validationException;
        }
        return true;
    }

    public function validateBrakeTestTypesClass3AndAbove(
        $exception,
        BrakeTestResultClass3AndAbove $brakeTestResultClass3AndAbove,
        $vehicleClass
    ) {
        $serviceBrake1TestTypeValid = !is_null($brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode())
            && $this->isValidBrakeTestTypeClass3AndAbove($brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode());
        $serviceBrake2TestTypeValid = is_null($brakeTestResultClass3AndAbove->getServiceBrake2TestType())
            || $this->isValidBrakeTestTypeClass3AndAbove($brakeTestResultClass3AndAbove->getServiceBrake2TestType()->getCode());
        $parkingBrakeTestTypeValid = !is_null($brakeTestResultClass3AndAbove->getParkingBrakeTestType())
            && $this->isValidBrakeTestTypeClass3AndAbove($brakeTestResultClass3AndAbove->getParkingBrakeTestType()->getCode());

        if (!$serviceBrake1TestTypeValid) {
            $this->addMessageToException(
                $exception,
                self::MESSAGE_SERVICE_INVALID_BRAKE_TEST_TYPE
            );
        }

        if (!$serviceBrake2TestTypeValid) {
            $this->addMessageToException(
                $exception,
                self::MESSAGE_SERVICE_INVALID_BRAKE_TEST_TYPE
            );
        }

        if ($brakeTestResultClass3AndAbove->getServiceBrake2TestType() !== null
            && $brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode()
            !== $brakeTestResultClass3AndAbove->getServiceBrake2TestType()->getCode()
        ) {
            $this->addMessageToException(
                $exception,
                self::MESSAGE_DIFFERENT_SERVICE_BRAKE_TYPES
            );
        }

        if (!$parkingBrakeTestTypeValid) {
            $this->addMessageToException(
                $exception,
                self::MESSAGE_PARKING_INVALID_BRAKE_TEST_TYPE
            );
        }

        if ($serviceBrake1TestTypeValid && $parkingBrakeTestTypeValid) {
            if (!BrakeTestTypeConfiguration::isValid(
                $vehicleClass,
                $brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode(),
                $brakeTestResultClass3AndAbove->getParkingBrakeTestType()->getCode()
            )) {
                $this->addMessageToException(
                    $exception,
                    self::MESSAGE_INVALID_COMBINATION_OF_BRAKE_TEST_TYPES
                );
            }
        }
    }

    public function validateBrakeTestConfigurationClass12(BrakeTestResultClass12 $brakeTestResultClass12)
    {
        $validationException = $this->getEmptyBadRequestException();

        $data = [
            'brakeTestType' => $brakeTestResultClass12->getBrakeTestType()->getCode(),
        ];

        if (!$this->validateBrakeTestTypesClass12($data)) {
            $this->addMessageToException(
                $validationException,
                self::MESSAGE_CONTROL1_INVALID_BRAKE_TEST_TYPE
            );
        }

        if ($this->effortsApplicableForClass1And2($brakeTestResultClass12->getBrakeTestType()->getCode())) {
            if (!$this->isPositiveInteger($brakeTestResultClass12->getVehicleWeightFront())
                || !$this->isPositiveInteger($brakeTestResultClass12->getVehicleWeightRear())
            ) {
                $this->addMessageToException($validationException, self::MESSAGE_INVALID_VEHICLE_WEIGHT);
            }

            if (!$this->isPositiveNumberOrZeroOrNullOrEmpty($brakeTestResultClass12->getRiderWeight())) {
                $this->addMessageToException($validationException, self::MESSAGE_INVALID_RIDER_WEIGHT);
            }

            if (!$this->isPositiveIntegerOrNull($brakeTestResultClass12->getSidecarWeight())) {
                $this->addMessageToException($validationException, self::MESSAGE_INVALID_SIDECAR_WEIGHT);
            }
        }

        if (count($validationException->getErrors())) {
            throw $validationException;
        }
        return true;
    }

    public function validateBrakeTestTypesClass12($data)
    {
        $brakeTestTypeValid
            = !empty($data['brakeTestType']) && $this->isValidBrakeTestTypeClass1And2($data['brakeTestType']);

        return $brakeTestTypeValid;
    }

    private function isValidBrakeTestTypeClass1And2($brakeTestType)
    {
        return in_array($brakeTestType, $this->validBrakeTestTypesClass12);
    }

    private function isValidBrakeTestTypeClass3AndAbove($brakeTestType)
    {
        return in_array($brakeTestType, $this->validBrakeTestTypesClass3AndAbove);
    }

    private function effortsApplicableForClass1And2($brakeTestType)
    {
        return in_array(
            $brakeTestType,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::FLOOR
            ]
        );
    }

    private function effortsApplicableForClass3AndAbove($brakeTestType)
    {
        return in_array(
            $brakeTestType,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE
            ]
        );
    }

    public function validateBrakeTestTypeClass1And2($brakeTestType)
    {
        $isValidType = $this->isValidBrakeTestTypeClass1And2($brakeTestType);

        if (!$isValidType) {
            throw new InvalidFieldValueException();
        }
    }

    public function validateBrakeTestTypeClass3AndAbove($brakeTestType)
    {
        $isValidType = $this->isValidBrakeTestTypeClass3AndAbove($brakeTestType);

        if (!$isValidType) {
            throw new InvalidFieldValueException();
        }
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResultClass3AndAbove
     * @param BadRequestException           $validationException
     */
    private function processWeightAndWeightType($brakeTestResultClass3AndAbove, $validationException)
    {
        $isWeightTypeProvided = !is_null($brakeTestResultClass3AndAbove->getWeightType());

        $weightRequiredValidation = !($isWeightTypeProvided &&
            $brakeTestResultClass3AndAbove->getWeightType()->getCode() === WeightSourceCode::NOT_APPLICABLE);

        if (!$isWeightTypeProvided) {
            $this->addMessageToException($validationException, self::MESSAGE_INVALID_VEHICLE_WEIGHT_TYPE);
        }

        if ($weightRequiredValidation) {
            if (!$this->isPositiveInteger($brakeTestResultClass3AndAbove->getVehicleWeight())) {
                $this->addMessageToException($validationException, self::MESSAGE_INVALID_VEHICLE_WEIGHT);
            }
        }
    }
}
