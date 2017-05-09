<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\BrakeTestTypeCode;
use PHPUnit_Framework_TestCase;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;

/**
 * Class BrakeTestResultServiceBrakeDataTest.
 */
class BrakeTestResultServiceBrakeDataTest extends PHPUnit_Framework_TestCase
{
    public function testSetsPropertiesCorrectlyServiceBrakeData()
    {
        $data = self::getTestData();
        $brakeTestResult = self::getTestBrakeTestResultServiceData();

        $this->assertEquals(
            $data['effortNearsideAxle1'],
            $brakeTestResult->getEffortNearsideAxle1()
        );
        $this->assertEquals(
            $data['effortOffsideAxle1'],
            $brakeTestResult->getEffortOffsideAxle1()
        );
        $this->assertEquals(
            $data['effortNearsideAxle2'],
            $brakeTestResult->getEffortNearsideAxle2()
        );
        $this->assertEquals(
            $data['effortOffsideAxle2'],
            $brakeTestResult->getEffortOffsideAxle2()
        );
        $this->assertEquals(
            $data['effortNearsideAxle3'],
            $brakeTestResult->getEffortNearsideAxle3()
        );
        $this->assertEquals(
            $data['effortOffsideAxle3'],
            $brakeTestResult->getEffortOffsideAxle3()
        );

        $this->assertEquals(
            $data['lockNearsideAxle1'],
            $brakeTestResult->getLockNearsideAxle1()
        );
        $this->assertEquals(
            $data['lockOffsideAxle1'],
            $brakeTestResult->getLockOffsideAxle1()
        );
        $this->assertEquals(
            $data['lockNearsideAxle2'],
            $brakeTestResult->getLockNearsideAxle2()
        );
        $this->assertEquals(
            $data['lockOffsideAxle2'],
            $brakeTestResult->getLockOffsideAxle2()
        );
        $this->assertEquals(
            $data['lockNearsideAxle3'],
            $brakeTestResult->getLockNearsideAxle3()
        );
        $this->assertEquals(
            $data['lockOffsideAxle3'],
            $brakeTestResult->getLockOffsideAxle3()
        );
        $this->assertEquals(
            $data['imbalanceAxle1'],
            $brakeTestResult->getImbalanceAxle1()
        );
        $this->assertEquals(
            $data['imbalanceAxle2'],
            $brakeTestResult->getImbalanceAxle2()
        );

        $this->assertEquals(
            $data['imbalancePass'],
            $brakeTestResult->getImbalancePass()
        );
    }

    public static function getTestBrakeTestResultServiceData()
    {
        $brakeTestResult = new BrakeTestResultServiceBrakeData();
        $data = self::getTestData();

        return $brakeTestResult
            ->setEffortNearsideAxle1($data['effortNearsideAxle1'])
            ->setEffortOffsideAxle1($data['effortOffsideAxle1'])
            ->setEffortNearsideAxle2($data['effortNearsideAxle2'])
            ->setEffortOffsideAxle2($data['effortOffsideAxle2'])
            ->setEffortNearsideAxle3($data['effortNearsideAxle3'])
            ->setEffortOffsideAxle3($data['effortOffsideAxle3'])
            ->setLockNearsideAxle1($data['lockNearsideAxle1'])
            ->setLockOffsideAxle1($data['lockOffsideAxle1'])
            ->setLockNearsideAxle2($data['lockNearsideAxle2'])
            ->setLockOffsideAxle2($data['lockOffsideAxle2'])
            ->setLockNearsideAxle3($data['lockNearsideAxle3'])
            ->setLockOffsideAxle3($data['lockOffsideAxle3'])
            ->setImbalanceAxle1($data['imbalanceAxle1'])
            ->setImbalanceAxle2($data['imbalanceAxle2'])
            ->setImbalanceAxle3($data['imbalanceAxle3'])
            ->setImbalancePass($data['imbalancePass']);
    }

    public static function getTestData()
    {
        return [
            'id' => 2,
            'testType' => BrakeTestTypeCode::ROLLER,
            'effortNearsideAxle1' => 11,
            'effortOffsideAxle1' => 12,
            'effortNearsideAxle2' => 13,
            'effortOffsideAxle2' => 14,
            'effortNearsideAxle3' => 41,
            'effortOffsideAxle3' => 42,
            'lockNearsideAxle1' => true,
            'lockOffsideAxle1' => false,
            'lockNearsideAxle2' => true,
            'lockOffsideAxle2' => false,
            'lockNearsideAxle3' => true,
            'lockOffsideAxle3' => false,
            'imbalanceAxle1' => 30,
            'imbalanceAxle2' => 31,
            'imbalanceAxle3' => 53,
            'imbalancePass' => true,
        ];
    }
}
