<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTest\Model;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use PHPUnit_Framework_TestCase;

class OdometerReadingViewObjectTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $readingViewObject = new OdometerReadingViewObject();

        $this->assertTrue(
            $readingViewObject->isModifiable(),
            '"modifiable" should initially be true'
        );

        $this->assertNull(
            $readingViewObject->getNotice(),
            '"notice" should initially be a value'
        );

        $this->assertFalse(
            $readingViewObject->hasNotice(),
            '"hasNotice" should initially be false'
        );

        $this->assertNull(
            $readingViewObject->getValue(),
            '"value" should initially be a value'
        );

        $this->assertNull(
            $readingViewObject->getUnit(),
            '"unit" should initially be a value'
        );

        $this->assertNull(
            $readingViewObject->getResultType(),
            '"resultType" should initially be a value'
        );

        $this->assertTrue(
            $readingViewObject->isNotRecorded(),
            '"isNotRecorded" should initially be true'
        );

        $this->assertEquals(
            'Not recorded',
            $readingViewObject->getDisplayValue(),
            '"displayValue" should initially be "not recorded"'
        );
    }

    public function testDisplayValues_incorrectEntryWithOkResult()
    {
        $readingViewObject = OdometerReadingViewObject::create(null, null);

        $this->assertEquals(OdometerReadingViewObject::IS_NOT_RECORDED, $readingViewObject->getDisplayValue());
    }

    public function testDisplayValues_givenReadingValuesMap_shouldReturnValueUnit()
    {
        $readingViewObject = OdometerReadingViewObject::create(23000, OdometerUnit::MILES);

        $this->assertEquals('23000 miles', $readingViewObject->getDisplayValue());
    }

    public function testDisplayValues_givenOdometerNotReadable_shouldReturnNotReadable()
    {
        $odometerViewObject = OdometerReadingViewObject::create(null, null, OdometerReadingResultType::NOT_READABLE);

        $this->assertEquals(OdometerReadingViewObject::IS_NOT_READABLE, $odometerViewObject->getDisplayValue());
    }

    public function testDisplayValues_givenNoOdometer_shouldReturnNoOdometer()
    {
        $odometerViewObject = OdometerReadingViewObject::create(null, null, OdometerReadingResultType::NO_ODOMETER);
        $this->assertEquals(OdometerReadingViewObject::IS_NOT_PRESENT, $odometerViewObject->getDisplayValue());
    }

    private function getReadingValueMapData($value = null, $unit = null, $resultType = null)
    {
        return [
            'value' => $value,
            'unit' => $unit,
            'resultType' => $resultType,
        ];
    }
}
