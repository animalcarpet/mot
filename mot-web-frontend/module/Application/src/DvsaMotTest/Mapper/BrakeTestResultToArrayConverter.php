<?php

namespace DvsaMotTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResult;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass1And2;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultServiceBrakeData;
use DvsaMotTest\Model\BrakeTestResultClass1And2ViewModel;
use DvsaMotTest\Model\BrakeTestResultClass3AndAboveViewModel;

/**
 * Class BrakeTestResultToArrayConverter
 * Converts BrakeTestResult to array to be used in BrakeTestResultClass_X_ViewModel classes
 * Helper class for BrakeTestResultsController
 * @package DvsaMotTest\Mapper
 */
class BrakeTestResultToArrayConverter
{
    /**
     * @param BrakeTestResult $brakeTestResult
     * @return array
     */
    public static function convert(BrakeTestResult $brakeTestResult)
    {
        if($brakeTestResult instanceof BrakeTestResultClass1And2) {
            return self::convertBrakeTestResultClass1And2($brakeTestResult);
        }

        if($brakeTestResult instanceof BrakeTestResultClass3AndAbove) {
            return self::convertBrakeTestResultClass3AndAbove($brakeTestResult);
        }
    }

    public static function convertBrakeTestResultClass1And2(BrakeTestResultClass1And2 $brakeTestResult)
    {
        $array = [
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_FRONT => $brakeTestResult->getControl1EffortFront(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_FRONT => $brakeTestResult->getControl1LockFront(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_REAR => $brakeTestResult->getControl1EffortRear(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_REAR => $brakeTestResult->getControl1LockRear(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_SIDECAR => $brakeTestResult->getControl1EffortSidecar(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_BRAKE_EFFICIENCY => $brakeTestResult->getControl1BrakeEfficiency(),

            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_FRONT => $brakeTestResult->getControl2EffortFront(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_FRONT => $brakeTestResult->getControl2LockFront(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_REAR => $brakeTestResult->getControl2EffortRear(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_REAR => $brakeTestResult->getControl2LockRear(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_SIDECAR => $brakeTestResult->getControl2EffortSidecar(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_BRAKE_EFFICIENCY => $brakeTestResult->getControl2BrakeEfficiency(),
        ];

        return $array;
    }

    public static function convertBrakeTestResultClass3AndAbove(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        /**
         * Dvsa\Mot\ApiClient\Resource\Item\AbstractItem from mot/api-client-php doesn't support property existence checking
         *
         * Because in response we have few properties that can be set null - e.g serviceBrake1Data and serviceBrake2Data
         * we can not rely on chaining getters on those objects eg:
         *
         * $brakeTestResult->getServiceBrake1Data()->getEffortNearsideAxel1()
         *
         * A walk around solution is to use raw $data stdClass obj and use tryGetProperty() method provided as defensive approach
         * to verify existence of a given property and handle nulls gracefully
         *
         */
        $data = $brakeTestResult->getData();

        $serviceBrake1Data = [
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_SINGLE => self::tryGetProperty($data->serviceBrake1Data, "lockSingle"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_SINGLE => self::tryGetProperty($data->serviceBrake1Data, "effortSingle"),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_SERVICE_EFFORT_NEARSIDE_AXLE_1 =>    self::tryGetProperty($data->serviceBrake1Data, "effortNearsideAxel1"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_NEARSIDE_AXLE_1 =>      self::tryGetProperty($data->serviceBrake1Data, "lockNearsideAxle1"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_OFFSIDE_AXLE_1 =>     self::tryGetProperty($data->serviceBrake1Data, "effortOffsideAxel1"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_OFFSIDE_AXLE_1 =>       self::tryGetProperty($data->serviceBrake1Data, "lockOffsideAxle1"),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_NEARSIDE_AXLE_2 =>    self::tryGetProperty($data->serviceBrake1Data, "effortNearsideAxel2"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_NEARSIDE_AXLE_2 =>      self::tryGetProperty($data->serviceBrake1Data, "lockNearsideAxle2"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_OFFSIDE_AXLE_2 =>     self::tryGetProperty($data->serviceBrake1Data, "effortOffsideAxel2"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_OFFSIDE_AXLE_2 =>       self::tryGetProperty($data->serviceBrake1Data, "lockOffsideAxle2"),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_NEARSIDE_AXLE_3 =>    self::tryGetProperty($data->serviceBrake1Data, "effortNearsideAxel3"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_NEARSIDE_AXLE_3 =>      self::tryGetProperty($data->serviceBrake1Data, "lockNearsideAxle3"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_OFFSIDE_AXLE_3 =>     self::tryGetProperty($data->serviceBrake1Data, "effortOffsideAxel3"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_OFFSIDE_AXLE_3 =>       self::tryGetProperty($data->serviceBrake1Data, "lockOffsideAxle3"),
        ];

        $serviceBrake2Data = [
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_SINGLE => self::tryGetProperty($data->serviceBrake2Data, "lockSingle"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_SINGLE => self::tryGetProperty($data->serviceBrake2Data, "effortSingle"),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_SERVICE_EFFORT_NEARSIDE_AXLE_1 =>  self::tryGetProperty($data->serviceBrake2Data, "effortNearsideAxel1"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_NEARSIDE_AXLE_1 =>    self::tryGetProperty($data->serviceBrake2Data, "lockNearsideAxle1"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_OFFSIDE_AXLE_1 =>   self::tryGetProperty($data->serviceBrake2Data, "effortOffsideAxel1"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_OFFSIDE_AXLE_1 =>     self::tryGetProperty($data->serviceBrake2Data, "lockOffsideAxle1"),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_NEARSIDE_AXLE_2 =>  self::tryGetProperty($data->serviceBrake2Data, "effortNearsideAxel2"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_NEARSIDE_AXLE_2 =>    self::tryGetProperty($data->serviceBrake2Data, "lockNearsideAxle2"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_OFFSIDE_AXLE_2 =>   self::tryGetProperty($data->serviceBrake2Data, "effortOffsideAxel2"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_OFFSIDE_AXLE_2 =>     self::tryGetProperty($data->serviceBrake2Data, "lockOffsideAxle2"),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_NEARSIDE_AXLE_3 =>  self::tryGetProperty($data->serviceBrake2Data, "effortNearsideAxel3"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_NEARSIDE_AXLE_3 =>    self::tryGetProperty($data->serviceBrake2Data, "lockNearsideAxle3"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_EFFORT_OFFSIDE_AXLE_3 =>   self::tryGetProperty($data->serviceBrake2Data, "effortOffsideAxel3"),
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_LOCK_OFFSIDE_AXLE_3 =>     self::tryGetProperty($data->serviceBrake2Data, "lockOffsideAxle3"),

        ];

        $array = [
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_EFFORT_SINGLE =>             self::tryGetProperty($data, 'parkingBrakeEffortSingle'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_LOCK_SINGLE =>               self::tryGetProperty($data, 'parkingBrakeLockSingle'),

            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_EFFORT_NEARSIDE =>           self::tryGetProperty($data, 'parkingBrakeEffortNearside'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_LOCK_NEARSIDE =>             self::tryGetProperty($data, 'parkingBrakeLockNearside'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_EFFORT_OFFSIDE =>            self::tryGetProperty($data, 'parkingBrakeEffortOffside'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_LOCK_OFFSIDE =>              self::tryGetProperty($data, 'parkingBrakeLockOffside'),

            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_EFFORT_SECONDARY_NEARSIDE => self::tryGetProperty($data, 'parkingBrakeEffortSecondaryNearside'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_LOCK_SECONDARY_NEARSIDE =>   self::tryGetProperty($data, 'parkingBrakeLockSecondaryNearside'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_EFFORT_SECONDARY_OFFSIDE =>  self::tryGetProperty($data, 'parkingBrakeEffortSecondaryOffside'),
            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_LOCK_SECONDARY_OFFSIDE =>    self::tryGetProperty($data, 'parkingBrakeLockSecondaryOffside'),

            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_BRAKE_1_DATA_KEY =>         $serviceBrake1Data,
            BrakeTestResultClass3AndAboveViewModel::API_SERVICE_BRAKE_2_DATA_KEY  =>        $serviceBrake2Data,

            BrakeTestResultClass3AndAboveViewModel::ID_SERVICE_1_EFFICIENCY =>              self::tryGetProperty($data, "serviceBrake1Efficiency"),
            BrakeTestResultClass3AndAboveViewModel::ID_SERVICE_2_EFFICIENCY =>              self::tryGetProperty($data, "serviceBrake2Efficiency"),

            BrakeTestResultClass3AndAboveViewModel::NAME_PARKING_EFFICIENCY_PASS =>         self::tryGetProperty($data, "serviceBrake1EfficiencyPass"),

            BrakeTestResultClass3AndAboveViewModel::ID_PARKING_EFFICIENCY =>                self::tryGetProperty($data, 'parkingBrakeEfficiency'),

        ];

        return $array;
    }

    private static function tryGetProperty($dataObj, $property)
    {
        if (is_null($dataObj))
        {
            return null;
        }

        if (!property_exists($dataObj, $property))
        {
            return null;
        }

        return $dataObj->$property;
    }
}