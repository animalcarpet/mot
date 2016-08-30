<?php

namespace DvsaMotApi\Service\Validator;

use DvsaCommon\Constants\BrakeTestConfigurationClass1And2;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\Vehicle;

/**
 * Class BrakeTestResultValidator
 */
class BrakeTestResultValidator extends AbstractValidator
{

    const MESSAGE_EFFORT_VALUE_TOO_LARGE = 'The value entered for %s is too large';
    const MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL = 'Efforts should be positive numbers or null';
    const VALUE_EFFORT_NOT_APPLICABLE = 'Efforts do not apply to this type of test';
    const BOOL_VALUE_MESSAGE_LOCK_TRUE_FALSE = 'Locks should be true/false values or null';
    const VALUE_LOCK_NOT_APPLICABLE = 'Locks do not apply to this type of test';
    const MESSAGE_FLOOR_ONLY_FRONT_APPLICABLE
        = 'Only one effort value for each control should be supplied for floor type test';
    const MESSAGE_FLOOR_FRONT_EFFORT_NUMBER = 'Effort should be a positive number';
    const MESSAGE_CONTROL_1_EMPTY = 'At least one of the effort values for control 1 must be filled';
    const MESSAGE_CONTROL_2_EMPTY = 'At least one of the effort values for control 2 must be filled';
    const MESSAGE_ONE_CONTROL_REAR = 'At least one of the controls has to have a value on the rear';
    const MESSAGE_ONE_CONTROL_FRONT = 'At least one of the controls has to have a value on the front';
    const MESSAGE_EFFICIENCY_REQUIRED_POSITIVE_NUMBER = 'Efficiency must be supplied for this type of test';
    const MESSAGE_EFFICIENCY_OUTSIDE_OF_RANGE = 'Efficiency must be in a range of 1 - 100%';
    const MESSAGE_SERVICE_BRAKE_DATA_NOT_ALLOWED = 'Service brake data does not apply for this type of test';
    const MESSAGE_EFFICIENCY_NOT_ALLOWED = 'Efficiency does not apply for this type of test';
    const MESSAGE_EFFICIENCY_PASS_NOT_ALLOWED = 'Efficiency pass does not apply for this type of test';
    const MESSAGE_EFFICIENCY_PASS_REQUIRED_BOOL = 'Efficiency pass must be supplied for this type of test';
    const MESSAGE_INVALID_TEST_TYPE = 'Invalid service / parking brake type combination';
    const MESSAGE_SERVICE_BRAKE_2_DATA_N_A = 'Service brake 2 is not applicable to this vehicle class';
    const MESSAGE_SINGLE_LINE_N_A_PAST_2010_SEP
        = "Single line service brake type is not applicable to vehicles past 1 Sep 2010";
    const MESSAGE_GRADIENT_CONTROLS_BELOW_BOOL
        = 'Controls below minimum flag for gradient type test should be boolean value';
    const MESSAGE_GRADIENT_CONTROLS_ABOVE_BOOL
        = 'Controls above upper minimum flag for gradient type test should be boolean value';
    const MESSAGE_GRADIENT_CONTROLS_MINIMUMS_INVALID
        = 'Controls can\'t be both above upper minimum and below minimum';
    const BRAKE_RESULT_MAX_VALUE = 9999;

    private $brakeEfforts
        = [
            'nearsideAxle1'                 => 'service brake nearside axle 1',
            'offsideAxle1'                  => 'service brake offside axle 1',
            'nearsideAxle2'                 => 'service brake nearside axle 2',
            'offsideAxle2'                  => 'service brake offside axle 2',
            'nearsideAxle3'                 => 'service brake nearside axle 3',
            'offsideAxle3'                  => 'service brake offside axle 3',
            'parkingBrakeNearside'          => 'parking brake nearside',
            'parkingBrakeOffside'           => 'parking brake offside',
            'parkingBrakeSecondaryOffside'  => 'parking brake secondary offside',
            'parkingBrakeSecondaryNearside' => 'parking brake secondary nearside',
            'parkingBrakeSingle'            => 'parking brake single',
        ];

    private $controls
        = [
            'control1EffortFront'   => 'control 1 effort front',
            'control2EffortFront'   => 'control 2 effort front',
            'control1EffortRear'    => 'control 1 effort rear',
            'control1EffortSidecar' => 'control 1 effort sidecar',
            'control2EffortRear'    => 'control 2 effort rear',
            'control2EffortSidecar' => 'control 2 effort sidecar',
        ];

    private function validateEfforts(ServiceException $validationException, $efforts, $brakeTestType)
    {
        switch ($brakeTestType) {
            case BrakeTestTypeCode::ROLLER:
            case BrakeTestTypeCode::PLATE:

                foreach ($efforts as $key => $value) {
                    if (!$this->isPositiveNumberOrZeroOrNull($value)) {
                        $this->addMessageToException(
                            $validationException,
                            self::MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL
                        );
                    }
                    if ($value > self::BRAKE_RESULT_MAX_VALUE) {
                        $this->addMessageToException(
                            $validationException,
                            sprintf(self::MESSAGE_EFFORT_VALUE_TOO_LARGE, $this->brakeEfforts[$key])
                        );
                    }
                }

                break;
            default:
                $this->validateValuesAreNull($validationException, $efforts, self::VALUE_EFFORT_NOT_APPLICABLE);
                break;
        }
    }

    private function validateValuesAreNull(ServiceException $validationException, $efforts, $message)
    {
        foreach ($efforts as $value) {
            if (!$this->isNull($value)) {
                $this->addMessageToException(
                    $validationException,
                    $message
                );
            }
        }
    }

    private function validateServiceBrakeData(
        ServiceException $validationException,
        Vehicle $vehicle,
        $serviceBrakeData,
        $isSingleInFront,
        $brakeTestType
    ) {
        switch ($brakeTestType) {
            case BrakeTestTypeCode::ROLLER:
            case BrakeTestTypeCode::PLATE:

                if ($serviceBrakeData === null) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL
                    );
                } else {
                    $vehicleClass = $vehicle->getVehicleClass()->getCode();
                    $this->validateEfforts(
                        $validationException,
                        $this->getValidEffortsByVehicleClass(
                            $serviceBrakeData,
                            $vehicleClass,
                            $isSingleInFront
                        ),
                        $brakeTestType
                    );
                    $this->validateValuesAreNull(
                        $validationException,
                        $this->getExpectedNullEffortsByVehicleClass(
                            $serviceBrakeData,
                            $vehicleClass,
                            $isSingleInFront
                        ),
                        self::VALUE_EFFORT_NOT_APPLICABLE
                    );

                    $this->validateLocks(
                        $validationException,
                        $this->getValidLocksByVehicleClass(
                            $serviceBrakeData,
                            $vehicleClass,
                            $isSingleInFront
                        ),
                        $brakeTestType,
                        [
                            BrakeTestTypeCode::ROLLER,
                            BrakeTestTypeCode::PLATE
                        ]
                    );
                    $this->validateValuesAreNull(
                        $validationException,
                        $this->getExpectedNullLocksByVehicleClass(
                            $serviceBrakeData,
                            $vehicleClass,
                            $isSingleInFront
                        ),
                        self::VALUE_LOCK_NOT_APPLICABLE
                    );
                };
                break;
            default:
                if ($serviceBrakeData !== null) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_SERVICE_BRAKE_DATA_NOT_ALLOWED
                    );
                } else {
                    // $service data not applicable and empty -> return
                    return;
                }
        }
    }

    private function getValidEffortsByVehicleClass(
        BrakeTestResultServiceBrakeData $serviceBrakeData,
        $vehicleClass,
        $isSingleInFront
    ) {
        if ($vehicleClass === Vehicle::VEHICLE_CLASS_3) {
            $efforts[] = $serviceBrakeData->getEffortSingle();
            if ($isSingleInFront === true) {
                $efforts[] = $serviceBrakeData->getEffortNearsideAxle2();
                $efforts[] = $serviceBrakeData->getEffortOffsideAxle2();
            } else {
                $efforts['nearsideAxle1'] = $serviceBrakeData->getEffortNearsideAxle1();
                $efforts['offsideAxle1'] = $serviceBrakeData->getEffortOffsideAxle1();
            }
        } else {
            $efforts['nearsideAxle1'] = $serviceBrakeData->getEffortNearsideAxle1();
            $efforts['offsideAxle1'] = $serviceBrakeData->getEffortOffsideAxle1();
            $efforts['nearsideAxle2'] = $serviceBrakeData->getEffortNearsideAxle2();
            $efforts['offsideAxle2'] = $serviceBrakeData->getEffortOffsideAxle2();
            $efforts['nearsideAxle3'] = $serviceBrakeData->getEffortNearsideAxle3();
            $efforts['offsideAxle3'] = $serviceBrakeData->getEffortOffsideAxle3();
        }
        return $efforts;
    }

    private function getExpectedNullEffortsByVehicleClass(
        BrakeTestResultServiceBrakeData $serviceBrakeData,
        $vehicleClass,
        $isSingleInFront
    ) {
        $efforts = [];
        if ($vehicleClass !== Vehicle::VEHICLE_CLASS_3) {
            $efforts['single'] = $serviceBrakeData->getEffortSingle();
        } else {
            if ($isSingleInFront === true) {
                $efforts[] = $serviceBrakeData->getEffortNearsideAxle1();
                $efforts[] = $serviceBrakeData->getEffortOffsideAxle1();
            } else {
                $efforts[] = $serviceBrakeData->getEffortNearsideAxle2();
                $efforts[] = $serviceBrakeData->getEffortOffsideAxle2();
            }
            $efforts[] = $serviceBrakeData->getEffortNearsideAxle3();
            $efforts[] = $serviceBrakeData->getEffortOffsideAxle3();
        }
        return $efforts;
    }

    private function getValidLocksByVehicleClass(
        BrakeTestResultServiceBrakeData $serviceBrakeData,
        $vehicleClass,
        $isSingleInFront
    ) {
        $locks = [];

        if ($vehicleClass === Vehicle::VEHICLE_CLASS_3) {
            $locks[] = $serviceBrakeData->getLockSingle();
            if ($isSingleInFront === true) {
                $locks[] = $serviceBrakeData->getLockNearsideAxle2();
                $locks[] = $serviceBrakeData->getLockOffsideAxle2();
            } else {
                $locks[] = $serviceBrakeData->getLockNearsideAxle1();
                $locks[] = $serviceBrakeData->getLockOffsideAxle1();
            }
        } else {
            $locks[] = $serviceBrakeData->getLockNearsideAxle1();
            $locks[] = $serviceBrakeData->getLockOffsideAxle1();
            $locks[] = $serviceBrakeData->getLockNearsideAxle2();
            $locks[] = $serviceBrakeData->getLockOffsideAxle2();
            $locks[] = $serviceBrakeData->getLockNearsideAxle3();
            $locks[] = $serviceBrakeData->getLockOffsideAxle3();
        }
        return $locks;
    }

    private function getExpectedNullLocksByVehicleClass(
        BrakeTestResultServiceBrakeData $serviceBrakeData,
        $vehicleClass,
        $isSingleInFront
    ) {
        $locks = [];
        if ($vehicleClass !== Vehicle::VEHICLE_CLASS_3) {
            $locks[] = $serviceBrakeData->getLockSingle();
        } else {
            if ($isSingleInFront === true) {
                $locks[] = $serviceBrakeData->getLockNearsideAxle1();
                $locks[] = $serviceBrakeData->getLockOffsideAxle1();
            } else {
                $locks[] = $serviceBrakeData->getLockNearsideAxle2();
                $locks[] = $serviceBrakeData->getLockOffsideAxle2();
            }
            $locks[] = $serviceBrakeData->getLockNearsideAxle3();
            $locks[] = $serviceBrakeData->getLockOffsideAxle3();
        }
        return $locks;
    }

    private function validateBrakeEfficiency(ServiceException $validationException, $brakeTestType, $efficiency)
    {
        $isEfficiencyValidNumber = $this->isPositiveNumber($efficiency);
        $isEfficiencyValidInRange = $this->isValueBetween($efficiency, 1, 100);
        switch ($brakeTestType) {
            case BrakeTestTypeCode::DECELEROMETER:
                if (!$isEfficiencyValidNumber) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_EFFICIENCY_REQUIRED_POSITIVE_NUMBER
                    );
                }
                if (!$isEfficiencyValidInRange) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_EFFICIENCY_OUTSIDE_OF_RANGE
                    );
                }
                break;
            default:
                if ($isEfficiencyValidNumber) {
                    $this->addMessageToException($validationException, self::MESSAGE_EFFICIENCY_NOT_ALLOWED);
                }
        }
    }

    private function validateBrakeEfficiencyPass(ServiceException $validationException, $brakeTestType, $efficiencyPass)
    {
        switch ($brakeTestType) {
            case BrakeTestTypeCode::GRADIENT:
                if (!$this->isBool($efficiencyPass)) {
                    $this->addMessageToException($validationException, self::MESSAGE_EFFICIENCY_PASS_REQUIRED_BOOL);
                }
                break;
            default:
                if (!$this->isNull($efficiencyPass)) {
                    $this->addMessageToException($validationException, self::MESSAGE_EFFICIENCY_PASS_NOT_ALLOWED);
                }
        }
    }

    private function validateClass3AndAboveRequiredFields(
        ServiceException $validationException,
        BrakeTestResultClass3AndAbove $brakeTestResult,
        Vehicle $vehicle
    ) {
        $vehicleClass = $vehicle->getVehicleClass()->getCode();
        $serviceBrake1TestType = $brakeTestResult->getServiceBrake1TestType()->getCode();
        $serviceBrake2TestType = $brakeTestResult->getServiceBrake2TestType()
            ? $brakeTestResult->getServiceBrake2TestType()->getCode() : null;
        $parkingBrakeTestType = $brakeTestResult->getParkingBrakeTestType()->getCode();

        $this->validateServiceBrakeData(
            $validationException,
            $vehicle,
            $brakeTestResult->getServiceBrake1Data(),
            $brakeTestResult->getIsSingleInFront(),
            $serviceBrake1TestType
        );

        $this->validateBrakeEfficiency(
            $validationException,
            $serviceBrake1TestType,
            $brakeTestResult->getServiceBrake1Efficiency()
        );
        $this->validateBrakeEfficiencyPass(
            $validationException,
            $serviceBrake1TestType,
            $brakeTestResult->getServiceBrake1EfficiencyPass()
        );

        // validate service brake 2 only if its type is known
        if ($brakeTestResult->getServiceBrake2TestType() !== null
            || $brakeTestResult->getServiceBrake2Data() !== null
        ) {
            if ($this->serviceBrake2DataNotApplicable($vehicleClass)) {
                $this->addMessageToException($validationException, self::MESSAGE_SERVICE_BRAKE_2_DATA_N_A);
            } else {
                $this->validateServiceBrakeData(
                    $validationException,
                    $vehicle,
                    $brakeTestResult->getServiceBrake2Data(),
                    $brakeTestResult->getIsSingleInFront(),
                    $brakeTestResult->getServiceBrake2TestType()->getCode()
                );

                $this->validateBrakeEfficiency(
                    $validationException,
                    $serviceBrake2TestType,
                    $brakeTestResult->getServiceBrake2Efficiency()
                );
                $this->validateBrakeEfficiencyPass(
                    $validationException,
                    $serviceBrake2TestType,
                    $brakeTestResult->getServiceBrake2EfficiencyPass()
                );
            }
        }

        // validate parking brake
        $this->validateEfforts(
            $validationException,
            [
                'parkingBrakeNearside'          => $brakeTestResult->getParkingBrakeEffortNearside(),
                'parkingBrakeOffside'           => $brakeTestResult->getParkingBrakeEffortOffside(),
                'parkingBrakeSecondaryOffside'  => $brakeTestResult->getParkingBrakeEffortSecondaryOffside(),
                'parkingBrakeSecondaryNearside' => $brakeTestResult->getParkingBrakeEffortSecondaryNearside(),
                'parkingBrakeSingle'            => $brakeTestResult->getParkingBrakeEffortSingle(),
            ],
            $parkingBrakeTestType
        );

        $this->validateLocks(
            $validationException,
            [
                $brakeTestResult->getParkingBrakeLockNearside(),
                $brakeTestResult->getParkingBrakeLockOffside(),
                $brakeTestResult->getParkingBrakeLockSecondaryOffside(),
                $brakeTestResult->getParkingBrakeLockSecondaryNearside(),
                $brakeTestResult->getParkingBrakeLockSingle(),
            ],
            $parkingBrakeTestType,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE
            ]
        );

        $this->validateBrakeEfficiency(
            $validationException,
            $parkingBrakeTestType,
            $brakeTestResult->getParkingBrakeEfficiency()
        );

        $this->validateBrakeEfficiencyPass(
            $validationException,
            $parkingBrakeTestType,
            $brakeTestResult->getParkingBrakeEfficiencyPass()
        );

        // validate isSingeLine for class 4, past sep 2010
        $class4VehicleLowerEfficiencyDate
            = new \DateTime(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY);
        if ($brakeTestResult->getServiceBrakeIsSingleLine()
            && $vehicleClass === Vehicle::VEHICLE_CLASS_4
            && $vehicle->getFirstUsedDate() >= $class4VehicleLowerEfficiencyDate
        ) {
            $this->addMessageToException($validationException, self::MESSAGE_SINGLE_LINE_N_A_PAST_2010_SEP);
        }
    }

    public function validateBrakeTestResultClass3AndAbove(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        Vehicle $vehicle
    ) {
        $validationException = $this->getEmptyBadRequestException();

        $this->validateClass3AndAboveRequiredFields($validationException, $brakeTestResult, $vehicle);

        $validationException->throwIfErrors();
    }

    public function validateBrakeTestResultClass1And2(BrakeTestResultClass12 $brakeTestResult, \DateTime $firstUsedDate)
    {
        $validationException = $this->getEmptyBadRequestException();

        $this->validateEffortsClass1And2($validationException, $brakeTestResult);

        $lockValues = [
            $brakeTestResult->getControl1LockFront(),
            $brakeTestResult->getControl1LockRear(),
            $brakeTestResult->getControl2LockFront(),
            $brakeTestResult->getControl2LockRear(),
        ];
        $this->validateLocks(
            $validationException,
            $lockValues,
            $brakeTestResult->getBrakeTestType()->getCode(),
            BrakeTestConfigurationClass1And2::$locksApplicable
        );

        $this->validateClass1And2Rules($validationException, $brakeTestResult, $firstUsedDate);

        if (count($validationException->getErrors())) {
            throw $validationException;
        }
    }

    protected function validateClass1And2Rules(
        $validationException,
        BrakeTestResultClass12 $brakeTestResult,
        \DateTime $firstUsedDate
    ) {
        $limitDate = new \DateTime(BrakeTestResultClass12::DATE_FIRST_USED_ONLY_ONE_CONTROL_ALLOWED);
        $firstUsedWhenOneControlAllowed = $firstUsedDate < $limitDate;
        switch ($brakeTestResult->getBrakeTestType()->getCode()) {
            case BrakeTestTypeCode::ROLLER:
            case BrakeTestTypeCode::PLATE:
                if ($brakeTestResult->getControl1EffortFront() === null
                    && $brakeTestResult->getControl1EffortRear() === null
                ) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_CONTROL_1_EMPTY
                    );
                }
                if (!$firstUsedWhenOneControlAllowed) {
                    if ($brakeTestResult->getControl2EffortFront() === null
                        && $brakeTestResult->getControl2EffortRear() === null
                    ) {
                        $this->addMessageToException(
                            $validationException,
                            self::MESSAGE_CONTROL_2_EMPTY
                        );
                    }
                    if ($brakeTestResult->getControl1EffortFront() === null
                        && $brakeTestResult->getControl2EffortFront() === null
                    ) {
                        $this->addMessageToException(
                            $validationException,
                            self::MESSAGE_ONE_CONTROL_FRONT
                        );
                    }
                    if ($brakeTestResult->getControl1EffortRear() === null
                        && $brakeTestResult->getControl2EffortRear() === null
                    ) {
                        $this->addMessageToException(
                            $validationException,
                            self::MESSAGE_ONE_CONTROL_REAR
                        );
                    }
                }
                break;
            case BrakeTestTypeCode::DECELEROMETER:
                if (!$this->isPositiveNumberOrZero($brakeTestResult->getControl1BrakeEfficiency())) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_EFFICIENCY_REQUIRED_POSITIVE_NUMBER
                    );
                }
                $control2Efficiency = $brakeTestResult->getControl2BrakeEfficiency();
                if (!$this->isPositiveNumberOrZeroOrNull($control2Efficiency)) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_EFFICIENCY_REQUIRED_POSITIVE_NUMBER
                    );
                } else {
                    if ($control2Efficiency === null && !$firstUsedWhenOneControlAllowed) {
                        $this->addMessageToException(
                            $validationException,
                            self::MESSAGE_EFFICIENCY_REQUIRED_POSITIVE_NUMBER
                        );
                    }
                }
                break;
            case BrakeTestTypeCode::GRADIENT:
                if (!$this->isBool($brakeTestResult->getGradientControl1BelowMinimum())) {
                    $this->addMessageToException($validationException, self::MESSAGE_GRADIENT_CONTROLS_BELOW_BOOL);
                }
                if (!$this->isBool($brakeTestResult->getGradientControl2BelowMinimum())) {
                    $this->addMessageToException($validationException, self::MESSAGE_GRADIENT_CONTROLS_BELOW_BOOL);
                }
                if (!$this->isBool($brakeTestResult->getGradientControl1AboveUpperMinimum())) {
                    $this->addMessageToException($validationException, self::MESSAGE_GRADIENT_CONTROLS_ABOVE_BOOL);
                }
                if (!$this->isBool($brakeTestResult->getGradientControl2AboveUpperMinimum())) {
                    $this->addMessageToException($validationException, self::MESSAGE_GRADIENT_CONTROLS_ABOVE_BOOL);
                }
                if ($brakeTestResult->getGradientControl1AboveUpperMinimum() === true
                    && $brakeTestResult->getGradientControl1BelowMinimum() === true
                ) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_GRADIENT_CONTROLS_MINIMUMS_INVALID
                    );
                }
                if ($brakeTestResult->getGradientControl2AboveUpperMinimum() === true
                    && $brakeTestResult->getGradientControl2BelowMinimum() === true
                ) {
                    $this->addMessageToException(
                        $validationException,
                        self::MESSAGE_GRADIENT_CONTROLS_MINIMUMS_INVALID
                    );
                }
                break;
        }
    }

    protected function validateLocks($exception, $lockValues, $brakeTestType, array $typeLocksApplicable)
    {
        switch ($brakeTestType) {
            case in_array($brakeTestType, $typeLocksApplicable):
                foreach ($lockValues as $value) {
                    if (!$this->isBoolOrNull($value)) {
                        $this->addMessageToException(
                            $exception,
                            self::BOOL_VALUE_MESSAGE_LOCK_TRUE_FALSE
                        );
                    }
                }
                break;
            default:
                foreach ($lockValues as $value) {
                    if (!$this->isNull($value)) {
                        $this->addMessageToException(
                            $exception,
                            self::VALUE_LOCK_NOT_APPLICABLE
                        );
                    }
                }
                break;
        }
    }

    protected function validateEffortsClass1And2(ServiceException $exception, BrakeTestResultClass12 $brakeTestResult)
    {
        $effortValuesFront = [
            'control1EffortFront' => $brakeTestResult->getControl1EffortFront(),
            'control2EffortFront' => $brakeTestResult->getControl2EffortFront(),
        ];
        $effortValuesRearAndSidecar = [
            'control1EffortRear'    => $brakeTestResult->getControl1EffortRear(),
            'control1EffortSidecar' => $brakeTestResult->getControl1EffortSidecar(),
            'control2EffortRear'    => $brakeTestResult->getControl2EffortRear(),
            'control2EffortSidecar' => $brakeTestResult->getControl2EffortSidecar(),
        ];
        $effortValues = array_merge($effortValuesFront, $effortValuesRearAndSidecar);
        $brakeTestType = $brakeTestResult->getBrakeTestType()->getCode();
        switch ($brakeTestType) {
            case BrakeTestTypeCode::ROLLER:
            case BrakeTestTypeCode::PLATE:
                foreach ($effortValues as $key => $value) {
                    if (!$this->isPositiveNumberOrZeroOrNull($value)) {
                        $this->addMessageToException(
                            $exception,
                            self::MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL
                        );
                    }
                    if ($value > self::BRAKE_RESULT_MAX_VALUE) {
                        $this->addMessageToException(
                            $exception,
                            sprintf(self::MESSAGE_EFFORT_VALUE_TOO_LARGE, $this->controls[$key])
                        );
                    }
                }
                break;
            case BrakeTestTypeCode::FLOOR:
                foreach ($effortValuesFront as $key => $value) {
                    if (!$this->isPositiveNumberOrZero($value)) {
                        $this->addMessageToException(
                            $exception,
                            self::MESSAGE_FLOOR_FRONT_EFFORT_NUMBER
                        );
                    }
                    if ($value > self::BRAKE_RESULT_MAX_VALUE) {
                        $this->addMessageToException(
                            $exception,
                            sprintf(self::MESSAGE_EFFORT_VALUE_TOO_LARGE, $this->controls[$key])
                        );
                    }
                }
                foreach ($effortValuesRearAndSidecar as $key => $value) {
                    if (!$this->isNull($value)) {
                        $this->addMessageToException(
                            $exception,
                            self::MESSAGE_FLOOR_ONLY_FRONT_APPLICABLE
                        );
                    }
                    if ($value > self::BRAKE_RESULT_MAX_VALUE) {
                        $this->addMessageToException(
                            $exception,
                            sprintf(self::MESSAGE_EFFORT_VALUE_TOO_LARGE, $this->controls[$key])
                        );
                    }
                }
                break;
            default:
                foreach ($effortValues as $key => $value) {
                    if (!$this->isNull($value)) {
                        $this->addMessageToException(
                            $exception,
                            self::VALUE_EFFORT_NOT_APPLICABLE
                        );
                    }
                    if ($value > self::BRAKE_RESULT_MAX_VALUE) {
                        $this->addMessageToException(
                            $exception,
                            sprintf(self::MESSAGE_EFFORT_VALUE_TOO_LARGE, $this->controls[$key])
                        );
                    }
                }
                break;
        }
    }

    private function serviceBrake2DataNotApplicable($vehicleClass)
    {
        return $vehicleClass === Vehicle::VEHICLE_CLASS_4
        || $vehicleClass === Vehicle::VEHICLE_CLASS_5
        || $vehicleClass === Vehicle::VEHICLE_CLASS_7;
    }
}
