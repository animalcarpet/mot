<?php

namespace DvsaMotTest\Model;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Data for brake test result class 3 and above view
 */
class BrakeTestResultClass3AndAboveViewModel
{
    const ID_PARKING_EFFORT_SINGLE = 'parkingBrakeEffortSingle';
    const ID_PARKING_LOCK_SINGLE = 'parkingBrakeLockSingle';

    const ID_PARKING_EFFORT_NEARSIDE = 'parkingBrakeEffortNearside';
    const ID_PARKING_LOCK_NEARSIDE = 'parkingBrakeLockNearside';
    const ID_PARKING_EFFORT_OFFSIDE = 'parkingBrakeEffortOffside';
    const ID_PARKING_LOCK_OFFSIDE = 'parkingBrakeLockOffside';

    const ID_PARKING_EFFORT_SECONDARY_NEARSIDE = 'parkingBrakeEffortSecondaryNearside';
    const ID_PARKING_LOCK_SECONDARY_NEARSIDE = 'parkingBrakeLockSecondaryNearside';
    const ID_PARKING_EFFORT_SECONDARY_OFFSIDE = 'parkingBrakeEffortSecondaryOffside';
    const ID_PARKING_LOCK_SECONDARY_OFFSIDE = 'parkingBrakeLockSecondaryOffside';

    const ID_SERVICE_LOCK_SINGLE = 'serviceBrakeLockSingle';
    const ID_SERVICE_EFFORT_SINGLE = 'serviceBrakeEffortSingle';

    const ID_SERVICE_2_LOCK_SINGLE = 'serviceBrake2LockSingle';
    const ID_SERVICE_2_EFFORT_SINGLE = 'serviceBrake2EffortSingle';

    const ID_SERVICE_EFFORT_NEARSIDE_AXLE_1 = 'serviceBrakeEffortNearsideAxle1';
    const ID_SERVICE_LOCK_NEARSIDE_AXLE_1 = 'serviceBrakeLockNearsideAxle1';
    const ID_SERVICE_EFFORT_OFFSIDE_AXLE_1 = 'serviceBrakeEffortOffsideAxle1';
    const ID_SERVICE_LOCK_OFFSIDE_AXLE_1 = 'serviceBrakeLockOffsideAxle1';

    const ID_SERVICE_EFFORT_NEARSIDE_AXLE_2 = 'serviceBrakeEffortNearsideAxle2';
    const ID_SERVICE_LOCK_NEARSIDE_AXLE_2 = 'serviceBrakeLockNearsideAxle2';
    const ID_SERVICE_EFFORT_OFFSIDE_AXLE_2 = 'serviceBrakeEffortOffsideAxle2';
    const ID_SERVICE_LOCK_OFFSIDE_AXLE_2 = 'serviceBrakeLockOffsideAxle2';

    const ID_SERVICE_EFFORT_NEARSIDE_AXLE_3 = 'serviceBrakeEffortNearsideAxle3';
    const ID_SERVICE_LOCK_NEARSIDE_AXLE_3 = 'serviceBrakeLockNearsideAxle3';
    const ID_SERVICE_EFFORT_OFFSIDE_AXLE_3 = 'serviceBrakeEffortOffsideAxle3';
    const ID_SERVICE_LOCK_OFFSIDE_AXLE_3 = 'serviceBrakeLockOffsideAxle3';

    const ID_SERVICE_2_EFFORT_NEARSIDE_AXLE_1 = 'serviceBrake2EffortNearsideAxle1';
    const ID_SERVICE_2_LOCK_NEARSIDE_AXLE_1 = 'serviceBrake2LockNearsideAxle1';
    const ID_SERVICE_2_EFFORT_OFFSIDE_AXLE_1 = 'serviceBrake2EffortOffsideAxle1';
    const ID_SERVICE_2_LOCK_OFFSIDE_AXLE_1 = 'serviceBrake2LockOffsideAxle1';

    const ID_SERVICE_2_EFFORT_NEARSIDE_AXLE_2 = 'serviceBrake2EffortNearsideAxle2';
    const ID_SERVICE_2_LOCK_NEARSIDE_AXLE_2 = 'serviceBrake2LockNearsideAxle2';
    const ID_SERVICE_2_EFFORT_OFFSIDE_AXLE_2 = 'serviceBrake2EffortOffsideAxle2';
    const ID_SERVICE_2_LOCK_OFFSIDE_AXLE_2 = 'serviceBrake2LockOffsideAxle2';

    const ID_SERVICE_2_EFFORT_NEARSIDE_AXLE_3 = 'serviceBrake2EffortNearsideAxle3';
    const ID_SERVICE_2_LOCK_NEARSIDE_AXLE_3 = 'serviceBrake2LockNearsideAxle3';
    const ID_SERVICE_2_EFFORT_OFFSIDE_AXLE_3 = 'serviceBrake2EffortOffsideAxle3';
    const ID_SERVICE_2_LOCK_OFFSIDE_AXLE_3 = 'serviceBrake2LockOffsideAxle3';

    const ID_SERVICE_1_EFFICIENCY = 'serviceBrake1Efficiency';
    const ID_SERVICE_2_EFFICIENCY = 'serviceBrake2Efficiency';

    const NAME_PARKING_EFFICIENCY_PASS = 'parkingBrakeEfficiencyPass';

    const ID_PARKING_EFFICIENCY = 'parkingBrakeEfficiency';

    private $parkingBrakeEffortSingle;
    private $parkingBrakeLockSingle;

    private $parkingBrakeEffortNearside;
    private $parkingBrakeLockNearside;
    private $parkingBrakeEffortOffside;
    private $parkingBrakeLockOffside;

    private $parkingBrakeEffortSecondaryNearside;
    private $parkingBrakeLockSecondaryNearside;
    private $parkingBrakeEffortSecondaryOffside;
    private $parkingBrakeLockSecondaryOffside;

    private $serviceBrakeLockSingle;
    private $serviceBrakeEffortSingle;

    private $serviceBrake2LockSingle;
    private $serviceBrake2EffortSingle;

    private $serviceBrakeEffortNearsideAxle1;
    private $serviceBrakeLockNearsideAxle1;
    private $serviceBrakeEffortOffsideAxle1;
    private $serviceBrakeLockOffsideAxle1;

    private $serviceBrakeEffortNearsideAxle2;
    private $serviceBrakeLockNearsideAxle2;
    private $serviceBrakeEffortOffsideAxle2;
    private $serviceBrakeLockOffsideAxle2;

    private $serviceBrakeEffortNearsideAxle3;
    private $serviceBrakeLockNearsideAxle3;
    private $serviceBrakeEffortOffsideAxle3;
    private $serviceBrakeLockOffsideAxle3;

    private $serviceBrake1Efficiency;
    private $serviceBrake2Efficiency;

    private $serviceBrake2EffortNearsideAxle1;
    private $serviceBrake2LockNearsideAxle1;
    private $serviceBrake2EffortOffsideAxle1;
    private $serviceBrake2LockOffsideAxle1;

    private $serviceBrake2EffortNearsideAxle2;
    private $serviceBrake2LockNearsideAxle2;
    private $serviceBrake2EffortOffsideAxle2;
    private $serviceBrake2LockOffsideAxle2;

    private $serviceBrake2EffortNearsideAxle3;
    private $serviceBrake2LockNearsideAxle3;
    private $serviceBrake2EffortOffsideAxle3;
    private $serviceBrake2LockOffsideAxle3;

    private $parkingBrakeEfficiencyPass;

    private $parkingBrakeEfficiency;

    private $serviceBrakeAxles;
    private $parkingBrakeAxles;

    /** @var  BrakeTestConfigurationClass3AndAboveHelper $brakeTestConfiguration */
    private $brakeTestConfiguration;

    /**
     * @param BrakeTestConfigurationClass3AndAboveDto $brakeTestConfigurationClass3AndAboveDto
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param array $apiData
     * @param array $postData
     */
    public function __construct(BrakeTestConfigurationClass3AndAboveDto $brakeTestConfigurationClass3AndAboveDto,
                                BrakeTestResultClass3AndAbove $brakeTestResult = null, $apiData, $postData)
    {
        if ($brakeTestConfigurationClass3AndAboveDto !== null) {
            $data = $postData === null ? $apiData : $postData;

            $this->brakeTestConfiguration = new BrakeTestConfigurationClass3AndAboveHelper($brakeTestConfigurationClass3AndAboveDto);

            $this->parkingBrakeEffortSingle = ArrayUtils::tryGet(
                $data,
                self::ID_PARKING_EFFORT_SINGLE
            );
            $this->parkingBrakeLockSingle = ArrayUtils::tryGet($data, self::ID_PARKING_LOCK_SINGLE);

            $this->parkingBrakeEffortNearside = ArrayUtils::tryGet($data, self::ID_PARKING_EFFORT_NEARSIDE);
            $this->parkingBrakeLockNearside = ArrayUtils::tryGet($data, self::ID_PARKING_LOCK_NEARSIDE);
            $this->parkingBrakeEffortOffside = ArrayUtils::tryGet($data, self::ID_PARKING_EFFORT_OFFSIDE);
            $this->parkingBrakeLockOffside = ArrayUtils::tryGet($data, self::ID_PARKING_LOCK_OFFSIDE);

            $this->parkingBrakeEffortSecondaryNearside = ArrayUtils::tryGet(
                $data,
                self::ID_PARKING_EFFORT_SECONDARY_NEARSIDE
            );
            $this->parkingBrakeLockSecondaryNearside = ArrayUtils::tryGet($data, self::ID_PARKING_LOCK_SECONDARY_NEARSIDE);
            $this->parkingBrakeEffortSecondaryOffside = ArrayUtils::tryGet(
                $data,
                self::ID_PARKING_EFFORT_SECONDARY_OFFSIDE
            );
            $this->parkingBrakeLockSecondaryOffside = ArrayUtils::tryGet($data, self::ID_PARKING_LOCK_SECONDARY_OFFSIDE);

            $this->parkingBrakeEfficiencyPass = ArrayUtils::tryGet($data, self::NAME_PARKING_EFFICIENCY_PASS);

            $this->parkingBrakeEfficiency = ArrayUtils::tryGet($data, self::ID_PARKING_EFFICIENCY);

            $this->serviceBrake1Efficiency = ArrayUtils::tryGet($data, self::ID_SERVICE_1_EFFICIENCY);
            $this->serviceBrake2Efficiency = ArrayUtils::tryGet($data, self::ID_SERVICE_2_EFFICIENCY);
            $this->serviceBrakeAxles = $brakeTestConfigurationClass3AndAboveDto->getNumberOfAxles();
            $this->parkingBrakeAxles = $brakeTestConfigurationClass3AndAboveDto->getParkingBrakeNumberOfAxles();
        } else {
            $this->parkingBrakeEffortSingle = $brakeTestResult->getParkingBrakeEffortSingle();
            $this->parkingBrakeLockSingle = $brakeTestResult->getParkingBrakeLockSingle();

            $this->parkingBrakeEffortNearside = $brakeTestResult->getParkingBrakeEffortNearside();
            $this->parkingBrakeLockNearside = $brakeTestResult->getParkingBrakeLockNearside();
            $this->parkingBrakeEffortOffside = $brakeTestResult->getParkingBrakeEffortOffside();
            $this->parkingBrakeLockOffside = $brakeTestResult->getParkingBrakeLockOffside();

            $this->parkingBrakeEffortSecondaryNearside = $brakeTestResult->getParkingBrakeEffortSecondaryNearside();
            $this->parkingBrakeLockSecondaryNearside = $brakeTestResult->getParkingBrakeLockSecondaryNearside();
            $this->parkingBrakeEffortSecondaryOffside = $brakeTestResult->getParkingBrakeEffortSecondaryOffside();
            $this->parkingBrakeLockSecondaryOffside = $brakeTestResult->getParkingBrakeLockSecondaryOffside();

            $this->parkingBrakeEfficiencyPass = $brakeTestResult->getParkingBrakeEfficiencyPass();

            $this->parkingBrakeEfficiency = $brakeTestResult->getParkingBrakeEfficiency();

            $this->serviceBrake1Efficiency = $brakeTestResult->getServiceBrake1Efficiency();
            $this->serviceBrake2Efficiency = $brakeTestResult->getServiceBrake2Efficiency();
            $this->serviceBrakeAxles = $brakeTestResult->getNumberOfAxles();
            $this->parkingBrakeAxles = $brakeTestResult->getParkingBrakeNumberOfAxles();
        }

        if ($postData !== null) {
            $this->serviceBrakeLockSingle = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_SINGLE);
            $this->serviceBrakeEffortSingle = ArrayUtils::tryGet($postData, self::ID_SERVICE_EFFORT_SINGLE);
            $this->serviceBrake2LockSingle = ArrayUtils::tryGet($postData, self::ID_SERVICE_2_LOCK_SINGLE);
            $this->serviceBrake2EffortSingle = ArrayUtils::tryGet($postData, self::ID_SERVICE_2_EFFORT_SINGLE);

            $this->serviceBrakeEffortNearsideAxle1 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_EFFORT_NEARSIDE_AXLE_1
            );
            $this->serviceBrakeLockNearsideAxle1 = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_NEARSIDE_AXLE_1);
            $this->serviceBrakeEffortOffsideAxle1 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_EFFORT_OFFSIDE_AXLE_1
            );
            $this->serviceBrakeLockOffsideAxle1 = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_OFFSIDE_AXLE_1);

            $this->serviceBrakeEffortNearsideAxle2 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_EFFORT_NEARSIDE_AXLE_2
            );

            $this->serviceBrakeLockNearsideAxle2 = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_NEARSIDE_AXLE_2);
            $this->serviceBrakeEffortOffsideAxle2 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_EFFORT_OFFSIDE_AXLE_2
            );
            $this->serviceBrakeLockOffsideAxle2 = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_OFFSIDE_AXLE_2);

            $this->serviceBrakeEffortNearsideAxle3 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_EFFORT_NEARSIDE_AXLE_3
            );
            $this->serviceBrakeLockNearsideAxle3 = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_NEARSIDE_AXLE_3);
            $this->serviceBrakeEffortOffsideAxle3 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_EFFORT_OFFSIDE_AXLE_3
            );
            $this->serviceBrakeLockOffsideAxle3 = ArrayUtils::tryGet($postData, self::ID_SERVICE_LOCK_OFFSIDE_AXLE_3);

            $this->serviceBrake2EffortNearsideAxle1 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_EFFORT_NEARSIDE_AXLE_1
            );
            $this->serviceBrake2LockNearsideAxle1 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_LOCK_NEARSIDE_AXLE_1
            );
            $this->serviceBrake2EffortOffsideAxle1 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_EFFORT_OFFSIDE_AXLE_1
            );
            $this->serviceBrake2LockOffsideAxle1 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_LOCK_OFFSIDE_AXLE_1
            );

            $this->serviceBrake2EffortNearsideAxle2 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_EFFORT_NEARSIDE_AXLE_2
            );
            $this->serviceBrake2LockNearsideAxle2 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_LOCK_NEARSIDE_AXLE_2
            );
            $this->serviceBrake2EffortOffsideAxle2 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_EFFORT_OFFSIDE_AXLE_2
            );
            $this->serviceBrake2LockOffsideAxle2 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_LOCK_OFFSIDE_AXLE_2
            );

            $this->serviceBrake2EffortNearsideAxle3 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_EFFORT_NEARSIDE_AXLE_3
            );
            $this->serviceBrake2LockNearsideAxle3 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_LOCK_NEARSIDE_AXLE_3
            );
            $this->serviceBrake2EffortOffsideAxle3 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_EFFORT_OFFSIDE_AXLE_3
            );
            $this->serviceBrake2LockOffsideAxle3 = ArrayUtils::tryGet(
                $postData,
                self::ID_SERVICE_2_LOCK_OFFSIDE_AXLE_3
            );
        } elseif ($apiData !== null) {
            $serviceBrake1Data = ArrayUtils::tryGet($apiData, 'serviceBrake1Data');

            $this->serviceBrakeLockSingle = ArrayUtils::tryGet($serviceBrake1Data, 'lockSingle');
            $this->serviceBrakeEffortSingle = ArrayUtils::tryGet($serviceBrake1Data, 'effortSingle');

            $this->serviceBrakeEffortNearsideAxle1 = ArrayUtils::tryGet($serviceBrake1Data, 'effortNearsideAxle1');
            $this->serviceBrakeLockNearsideAxle1 = ArrayUtils::tryGet($serviceBrake1Data, 'lockNearsideAxle1');
            $this->serviceBrakeEffortOffsideAxle1 = ArrayUtils::tryGet($serviceBrake1Data, 'effortOffsideAxle1');
            $this->serviceBrakeLockOffsideAxle1 = ArrayUtils::tryGet($serviceBrake1Data, 'lockOffsideAxle1');

            $this->serviceBrakeEffortNearsideAxle2 = ArrayUtils::tryGet($serviceBrake1Data, 'effortNearsideAxle2');
            $this->serviceBrakeLockNearsideAxle2 = ArrayUtils::tryGet($serviceBrake1Data, 'lockNearsideAxle2');
            $this->serviceBrakeEffortOffsideAxle2 = ArrayUtils::tryGet($serviceBrake1Data, 'effortOffsideAxle2');
            $this->serviceBrakeLockOffsideAxle2 = ArrayUtils::tryGet($serviceBrake1Data, 'lockOffsideAxle2');

            $this->serviceBrakeEffortNearsideAxle3 = ArrayUtils::tryGet($serviceBrake1Data, 'effortNearsideAxle3');
            $this->serviceBrakeLockNearsideAxle3 = ArrayUtils::tryGet($serviceBrake1Data, 'lockNearsideAxle3');
            $this->serviceBrakeEffortOffsideAxle3 = ArrayUtils::tryGet($serviceBrake1Data, 'effortOffsideAxle3');
            $this->serviceBrakeLockOffsideAxle3 = ArrayUtils::tryGet($serviceBrake1Data, 'lockOffsideAxle3');

            $serviceBrake2Data = ArrayUtils::tryGet($apiData, 'serviceBrake2Data');

            $this->serviceBrake2LockSingle = ArrayUtils::tryGet($serviceBrake2Data, 'lockSingle');
            $this->serviceBrake2EffortSingle = ArrayUtils::tryGet($serviceBrake2Data, 'effortSingle');

            $this->serviceBrake2EffortNearsideAxle1 = ArrayUtils::tryGet($serviceBrake2Data, 'effortNearsideAxle1');
            $this->serviceBrake2LockNearsideAxle1 = ArrayUtils::tryGet($serviceBrake2Data, 'lockNearsideAxle1');
            $this->serviceBrake2EffortOffsideAxle1 = ArrayUtils::tryGet($serviceBrake2Data, 'effortOffsideAxle1');
            $this->serviceBrake2LockOffsideAxle1 = ArrayUtils::tryGet($serviceBrake2Data, 'lockOffsideAxle1');

            $this->serviceBrake2EffortNearsideAxle2 = ArrayUtils::tryGet($serviceBrake2Data, 'effortNearsideAxle2');
            $this->serviceBrake2LockNearsideAxle2 = ArrayUtils::tryGet($serviceBrake2Data, 'lockNearsideAxle2');
            $this->serviceBrake2EffortOffsideAxle2 = ArrayUtils::tryGet($serviceBrake2Data, 'effortOffsideAxle2');
            $this->serviceBrake2LockOffsideAxle2 = ArrayUtils::tryGet($serviceBrake2Data, 'lockOffsideAxle2');

            $this->serviceBrake2EffortNearsideAxle3 = ArrayUtils::tryGet($serviceBrake2Data, 'effortNearsideAxle3');
            $this->serviceBrake2LockNearsideAxle3 = ArrayUtils::tryGet($serviceBrake2Data, 'lockNearsideAxle3');
            $this->serviceBrake2EffortOffsideAxle3 = ArrayUtils::tryGet($serviceBrake2Data, 'effortOffsideAxle3');
            $this->serviceBrake2LockOffsideAxle3 = ArrayUtils::tryGet($serviceBrake2Data, 'lockOffsideAxle3');
        }
    }

    public function toArray()
    {
        $data = [];

        if ($this->brakeTestConfiguration->effortsApplicableToFirstServiceBrake()) {
            $sb1Data = &$data['serviceBrake1Data'];
            if ($this->brakeTestConfiguration->isSingleWheelInFront()) {
                $sb1Data['effortSingle'] = $this->serviceBrakeEffortSingle;
                if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                    $sb1Data['lockSingle'] = $this->getServiceBrakeLockSingle();
                }
            }

            if ($this->brakeTestConfiguration->isSingleWheelInFront() === null
                || $this->brakeTestConfiguration->isSingleWheelInFront() === false
            ) {
                $sb1Data['effortNearsideAxle1'] = $this->serviceBrakeEffortNearsideAxle1;
                $sb1Data['effortOffsideAxle1'] = $this->serviceBrakeEffortOffsideAxle1;

                if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                    $sb1Data['lockNearsideAxle1'] = $this->getServiceBrakeLockNearsideAxle1();
                    $sb1Data['lockOffsideAxle1'] = $this->getServiceBrakeLockOffsideAxle1();
                }
            }
            if ($this->brakeTestConfiguration->isSingleWheelInFront() === null
                || $this->brakeTestConfiguration->isSingleWheelInFront() === true
            ) {
                $sb1Data['effortNearsideAxle2'] = $this->serviceBrakeEffortNearsideAxle2;
                $sb1Data['effortOffsideAxle2'] = $this->serviceBrakeEffortOffsideAxle2;
                if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                    $sb1Data['lockNearsideAxle2'] = $this->getServiceBrakeLockNearsideAxle2();
                    $sb1Data['lockOffsideAxle2'] = $this->getServiceBrakeLockOffsideAxle2();
                }
            }
            if ($this->brakeTestConfiguration->hasThreeAxles()) {
                $sb1Data['effortNearsideAxle3'] = $this->serviceBrakeEffortNearsideAxle3;
                $sb1Data['effortOffsideAxle3'] = $this->serviceBrakeEffortOffsideAxle3;
                if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                    $sb1Data['lockNearsideAxle3'] = $this->getServiceBrakeLockNearsideAxle3();
                    $sb1Data['lockOffsideAxle3'] = $this->getServiceBrakeLockOffsideAxle3();
                }
            }
            if ($this->brakeTestConfiguration->isSingleWheelInFront() === false) {
                $sb1Data['effortSingle'] = $this->serviceBrakeEffortSingle;
                if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                    $sb1Data['lockSingle'] = $this->getServiceBrakeLockSingle();
                }
            }
        } else {
            $data['serviceBrake1Efficiency'] = $this->getServiceBrake1Efficiency();
        }

        if ($this->brakeTestConfiguration->hasTwoServiceBrakes()) {
            $sb2Data = &$data['serviceBrake2Data'];
            if ($this->brakeTestConfiguration->effortsApplicableToFirstServiceBrake()) {
                if ($this->brakeTestConfiguration->isSingleWheelInFront()) {
                    $sb2Data['effortSingle'] = $this->serviceBrake2EffortSingle;
                    if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                        $sb2Data['lockSingle'] = $this->getServiceBrake2LockSingle();
                    }
                }
                if ($this->brakeTestConfiguration->isSingleWheelInFront() === null
                    || $this->brakeTestConfiguration->isSingleWheelInFront() === false
                ) {
                    $sb2Data['effortNearsideAxle1'] = $this->serviceBrakeEffortNearsideAxle1;
                    $sb2Data['effortOffsideAxle1'] = $this->serviceBrakeEffortOffsideAxle1;
                    if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                        $sb2Data['lockNearsideAxle1'] = $this->getServiceBrakeLockNearsideAxle1();
                        $sb2Data['lockOffsideAxle1'] = $this->getServiceBrakeLockOffsideAxle1();
                    }
                }
                if ($this->brakeTestConfiguration->isSingleWheelInFront() === null
                    || $this->brakeTestConfiguration->isSingleWheelInFront() === true
                ) {
                    $sb2Data['effortNearsideAxle2'] = $this->serviceBrakeEffortNearsideAxle2;
                    $sb2Data['effortOffsideAxle2'] = $this->serviceBrakeEffortOffsideAxle2;
                    if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                        $sb2Data['lockNearsideAxle2'] = $this->getServiceBrakeLockNearsideAxle2();
                        $sb2Data['lockOffsideAxle2'] = $this->getServiceBrakeLockOffsideAxle2();
                    }
                }
                if ($this->brakeTestConfiguration->isSingleWheelInFront() === false) {
                    $sb2Data['effortSingle'] = $this->serviceBrakeEffortSingle;
                    if ($this->brakeTestConfiguration->locksApplicableToFirstServiceBrake()) {
                        $sb2Data['lockSingle'] = $this->getServiceBrakeLockSingle();
                    }
                }
            } else {
                $data['serviceBrake2Efficiency'] = $this->getServiceBrake2Efficiency();
            }
        }
        if ($this->brakeTestConfiguration->isParkingBrakeTypeRollerOrPlate()) {
            $data[self::ID_PARKING_EFFORT_SINGLE] = $this->parkingBrakeEffortSingle;
            if ($this->brakeTestConfiguration->locksApplicableToParkingBrake()) {
                $data[self::ID_PARKING_LOCK_SINGLE] = $this->getParkingBrakeLockSingle();
            }
            if ($this->brakeTestConfiguration->isParkingBrakeOnTwoWheels()) {
                $data[self::ID_PARKING_EFFORT_NEARSIDE] = $this->parkingBrakeEffortNearside;
                $data[self::ID_PARKING_EFFORT_OFFSIDE] = $this->parkingBrakeEffortOffside;
                if ($this->brakeTestConfiguration->locksApplicableToParkingBrake()) {
                    $data[self::ID_PARKING_LOCK_NEARSIDE] = $this->getParkingBrakeLockNearside();
                    $data[self::ID_PARKING_LOCK_OFFSIDE] = $this->getParkingBrakeLockOffside();
                }
            }
            if ($this->brakeTestConfiguration->isParkingBrakeOnTwoAxles()) {
                $data[self::ID_PARKING_EFFORT_SECONDARY_NEARSIDE] = $this->parkingBrakeEffortSecondaryNearside;
                $data[self::ID_PARKING_EFFORT_SECONDARY_OFFSIDE] = $this->parkingBrakeEffortSecondaryOffside;
                if ($this->brakeTestConfiguration->locksApplicableToParkingBrake()) {
                    $data[self::ID_PARKING_LOCK_SECONDARY_NEARSIDE] = $this->getParkingBrakeLockSecondaryNearside();
                    $data[self::ID_PARKING_LOCK_SECONDARY_OFFSIDE] = $this->getParkingBrakeLockSecondaryOffside();
                }
            }
        } elseif ($this->brakeTestConfiguration->isParkingBrakeGradientType()) {
            $data['parkingBrakeEfficiencyPass'] = $this->getParkingBrakeEfficiencyPass();
        } else {
            $data['parkingBrakeEfficiency'] = $this->getParkingBrakeEfficiency();
        }

        $configurationDto = $this->brakeTestConfiguration->getConfigDto();
        return array_merge(
            $data,
            DtoHydrator::dtoToJson($configurationDto)
        );
    }

    /**
     * @return \DvsaMotTest\Model\BrakeTestConfigurationClass3AndAboveHelper
     */
    public function getBrakeTestConfiguration()
    {
        return $this->brakeTestConfiguration;
    }

    public function getParkingBrakeEffortSingle()
    {
        return $this->parkingBrakeEffortSingle;
    }

    public function getParkingBrakeLockSingle()
    {
        return $this->parkingBrakeLockSingle ? (bool)$this->parkingBrakeLockSingle : null;
    }

    public function getParkingBrakeEffortNearside()
    {
        return $this->parkingBrakeEffortNearside;
    }

    public function getParkingBrakeEffortOffside()
    {
        return $this->parkingBrakeEffortOffside;
    }

    public function getParkingBrakeEffortSecondaryNearside()
    {
        return $this->parkingBrakeEffortSecondaryNearside;
    }

    public function getParkingBrakeEffortSecondaryOffside()
    {
        return $this->parkingBrakeEffortSecondaryOffside;
    }

    public function getParkingBrakeLockNearside()
    {
        return $this->parkingBrakeLockNearside ? (bool)$this->parkingBrakeLockNearside : null;
    }

    public function getParkingBrakeLockOffside()
    {
        return $this->parkingBrakeLockOffside ? (bool)$this->parkingBrakeLockOffside : null;
    }

    public function getParkingBrakeLockSecondaryNearside()
    {
        return $this->parkingBrakeLockSecondaryNearside ? (bool)$this->parkingBrakeLockSecondaryNearside : null;
    }

    public function getParkingBrakeLockSecondaryOffside()
    {
        return $this->parkingBrakeLockSecondaryOffside ? (bool)$this->parkingBrakeLockSecondaryOffside : null;
    }

    public function getServiceBrake2EffortSingle()
    {
        return $this->serviceBrake2EffortSingle;
    }

    public function getServiceBrake2LockSingle()
    {
        return $this->serviceBrake2LockSingle ? (bool)$this->serviceBrake2LockSingle : null;
    }

    public function getServiceBrakeEffortSingle()
    {
        return $this->serviceBrakeEffortSingle;
    }

    public function getServiceBrakeLockSingle()
    {
        return $this->serviceBrakeLockSingle ? (bool)$this->serviceBrakeLockSingle : null;
    }

    public function getServiceBrake2EffortNearsideAxle1()
    {
        return $this->serviceBrake2EffortNearsideAxle1;
    }

    public function getServiceBrake2EffortNearsideAxle2()
    {
        return $this->serviceBrake2EffortNearsideAxle2;
    }

    public function getServiceBrake2EffortNearsideAxle3()
    {
        return $this->serviceBrake2EffortNearsideAxle3;
    }

    public function getServiceBrake2EffortOffsideAxle1()
    {
        return $this->serviceBrake2EffortOffsideAxle1;
    }

    public function getServiceBrake2EffortOffsideAxle2()
    {
        return $this->serviceBrake2EffortOffsideAxle2;
    }

    public function getServiceBrake2EffortOffsideAxle3()
    {
        return $this->serviceBrake2EffortOffsideAxle3;
    }

    public function getServiceBrake2LockNearsideAxle1()
    {
        return $this->serviceBrake2LockNearsideAxle1 ? (bool)$this->serviceBrake2LockNearsideAxle1 : null;
    }

    public function getServiceBrake2LockNearsideAxle2()
    {
        return $this->serviceBrake2LockNearsideAxle2 ? (bool)$this->serviceBrake2LockNearsideAxle2 : null;
    }

    public function getServiceBrake2LockNearsideAxle3()
    {
        return $this->serviceBrake2LockNearsideAxle3 ? (bool)$this->serviceBrake2LockNearsideAxle3 : null;
    }

    public function getServiceBrake2LockOffsideAxle1()
    {
        return $this->serviceBrake2LockOffsideAxle1 ? (bool)$this->serviceBrake2LockOffsideAxle1 : null;
    }

    public function getServiceBrake2LockOffsideAxle2()
    {
        return $this->serviceBrake2LockOffsideAxle2 ? (bool)$this->serviceBrake2LockOffsideAxle2 : null;
    }

    public function getServiceBrake2LockOffsideAxle3()
    {
        return $this->serviceBrake2LockOffsideAxle3 ? (bool)$this->serviceBrake2LockOffsideAxle3 : null;
    }

    public function getServiceBrakeEffortNearsideAxle1()
    {
        return $this->serviceBrakeEffortNearsideAxle1;
    }

    public function getServiceBrakeEffortNearsideAxle2()
    {
        return $this->serviceBrakeEffortNearsideAxle2;
    }

    public function getServiceBrakeEffortNearsideAxle3()
    {
        return $this->serviceBrakeEffortNearsideAxle3;
    }

    public function getServiceBrakeEffortOffsideAxle1()
    {
        return $this->serviceBrakeEffortOffsideAxle1;
    }

    public function getServiceBrakeEffortOffsideAxle2()
    {
        return $this->serviceBrakeEffortOffsideAxle2;
    }

    public function getServiceBrakeEffortOffsideAxle3()
    {
        return $this->serviceBrakeEffortOffsideAxle3;
    }

    public function getServiceBrakeLockNearsideAxle1()
    {
        return $this->serviceBrakeLockNearsideAxle1 ? (bool)$this->serviceBrakeLockNearsideAxle1 : null;
    }

    public function getServiceBrakeLockNearsideAxle2()
    {
        return $this->serviceBrakeLockNearsideAxle2 ? (bool)$this->serviceBrakeLockNearsideAxle2 : null;
    }

    public function getServiceBrakeLockNearsideAxle3()
    {
        return $this->serviceBrakeLockNearsideAxle3 ? (bool)$this->serviceBrakeLockNearsideAxle3 : null;
    }

    public function getServiceBrakeLockOffsideAxle1()
    {
        return $this->serviceBrakeLockOffsideAxle1 ? (bool)$this->serviceBrakeLockOffsideAxle1 : null;
    }

    public function getServiceBrakeLockOffsideAxle2()
    {
        return $this->serviceBrakeLockOffsideAxle2 ? (bool)$this->serviceBrakeLockOffsideAxle2 : null;
    }

    public function getServiceBrakeLockOffsideAxle3()
    {
        return $this->serviceBrakeLockOffsideAxle3 ? (bool)$this->serviceBrakeLockOffsideAxle3 : null;
    }

    public function getServiceBrake1Efficiency()
    {
        return $this->serviceBrake1Efficiency ? (int)$this->serviceBrake1Efficiency : null;
    }

    public function getServiceBrake2Efficiency()
    {
        return $this->serviceBrake2Efficiency ? (int)$this->serviceBrake2Efficiency : null;
    }

    public function getParkingBrakeEfficiency()
    {
        return $this->parkingBrakeEfficiency ? (int)$this->parkingBrakeEfficiency : null;
    }

    public function getParkingBrakeEfficiencyPass()
    {
        return isset($this->parkingBrakeEfficiencyPass) ? (bool)$this->parkingBrakeEfficiencyPass : null;
    }

    public function getServiceBrakeAxles()
    {
        return $this->serviceBrakeAxles;
    }

    public function getParkingBrakeAxles()
    {
        return $this->parkingBrakeAxles;
    }

    /**
     * This is to inject the brake test result's serviceBrake1Data data-set, coming from mot-test-service to
     * the existing model, until a proper refactoring is possible
     *
     * @param MotTest $motTest
     */
    public function setServiceBrake1Data(MotTest $motTest)
    {
        $brakeTestResult =$motTest->getBrakeTestResult();
        $serviceBrake1Data = $brakeTestResult->serviceBrake1Data;
        $parkingBrakeEfficiency = $motTest->getBrakeTestResult()->parkingBrakeEfficiency;

        $this->serviceBrakeEffortNearsideAxle1 = $serviceBrake1Data->effortNearsideAxel1;
        $this->serviceBrakeEffortNearsideAxle2 = $serviceBrake1Data->effortNearsideAxel2;
        $this->serviceBrakeEffortNearsideAxle3 = $serviceBrake1Data->effortNearsideAxel3;
        $this->serviceBrakeEffortOffsideAxle1 = $serviceBrake1Data->effortOffsideAxel1;
        $this->serviceBrakeEffortOffsideAxle2 = $serviceBrake1Data->effortOffsideAxel2;
        $this->serviceBrakeEffortOffsideAxle3 = $serviceBrake1Data->effortOffsideAxel3;
        $this->serviceBrakeEffortSingle = $serviceBrake1Data->effortSingle;
        $this->serviceBrakeLockNearsideAxle1 = $serviceBrake1Data->lockNearsideAxle1;
        $this->serviceBrakeLockNearsideAxle2 = $serviceBrake1Data->lockNearsideAxle2;
        $this->serviceBrakeLockNearsideAxle3 = $serviceBrake1Data->lockNearsideAxle3;
        $this->serviceBrakeLockOffsideAxle1 = $serviceBrake1Data->lockOffsideAxle1;
        $this->serviceBrakeLockOffsideAxle2 = $serviceBrake1Data->lockOffsideAxle2;
        $this->serviceBrakeLockOffsideAxle3 = $serviceBrake1Data->lockOffsideAxle3;
        $this->serviceBrakeLockSingle = $serviceBrake1Data->lockSingle;

        $this->parkingBrakeEffortNearside =  $brakeTestResult->parkingBrakeEffortNearside;
        $this->parkingBrakeEffortOffside =  $brakeTestResult->parkingBrakeEffortOffside;
        $this->parkingBrakeLockNearside=  $brakeTestResult->parkingBrakeLockNearside;
        $this->parkingBrakeLockOffside=  $brakeTestResult->parkingBrakeLockOffside;

        $this->parkingBrakeEffortSecondaryNearside =  $brakeTestResult->parkingBrakeEffortSecondaryNearside;
        $this->parkingBrakeEffortSecondaryOffside =  $brakeTestResult->parkingBrakeEffortSecondaryOffside;
        $this->parkingBrakeLockSecondaryNearside =  $brakeTestResult->parkingBrakeLockSecondaryNearside;
        $this->parkingBrakeLockSecondaryOffside =  $brakeTestResult->parkingBrakeLockSecondaryOffside;

        $this->parkingBrakeEfficiency = $parkingBrakeEfficiency;
    }
}
