<?php
namespace DvsaMotTest\Model;

use PHPUnit_Framework_TestCase;

class OdometerReadingViewObjectTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $odometerReadingViewObject = new OdometerReadingViewObject();

        $this->assertTrue(
            $odometerReadingViewObject->getModifiable(),
            '"modifiable" should initially be true'
        );

        $this->assertNull(
            $odometerReadingViewObject->getNotice(),
            '"notice" should initially be a value'
        );

        $this->assertFalse(
            $odometerReadingViewObject->hasNotice(),
            '"hasNotice" should initially be false'
        );

        $this->assertNull(
            $odometerReadingViewObject->getValue(),
            '"value" should initially be a value'
        );

        $this->assertNull(
            $odometerReadingViewObject->getUnit(),
            '"unit" should initially be a value'
        );

        $this->assertNull(
            $odometerReadingViewObject->getResultType(),
            '"resultType" should initially be a value'
        );

        $this->assertTrue(
            $odometerReadingViewObject->isNotRecorded(),
            '"isNotRecorded" should initially be true'
        );

        $this->assertEquals(
            'Not recorded',
            $odometerReadingViewObject->getDisplayValue(),
            '"displayValue" should initially be "not recorded"'
        );
    }

    public function testDisplayValues_givenReadingValuesMap_shouldReturnValueUnit()
    {
        $readingValueMap = $this->getReadingValueMapData(23000, 'mi');

        $odometerReadingViewObject = new OdometerReadingViewObject();
        $odometerReadingViewObject->setOdometerReadingValuesMap($readingValueMap);

        $odometerReadingViewObject->getDisplayValue();
    }

    public function testDisplayValues_givenOdometerNotReadable_shouldReturnNotReadable()
    {
        $readingValueMap = $this->getReadingValueMapData(null, null, 'NOT_READ');

        $odometerReadingViewObject = new OdometerReadingViewObject();
        $odometerReadingViewObject->setOdometerReadingValuesMap($readingValueMap);
        $this->assertEquals('Unreadable', $odometerReadingViewObject->getDisplayValue());
    }

    public function testDisplayValues_givenNoOdometer_shouldReturnNoOdometer()
    {
        $readingValueMap = $this->getReadingValueMapData(null, null, 'NO_METER');
        $odometerReadingViewObject = new OdometerReadingViewObject();
        $odometerReadingViewObject->setOdometerReadingValuesMap($readingValueMap);
        $this->assertEquals('Missing', $odometerReadingViewObject->getDisplayValue());
    }

    private function getReadingValueMapData($value = null, $unit = null, $resultType = null)
    {
        return [
            'value'      => $value,
            'unit'       => $unit,
            'resultType' => $resultType
        ];
    }
}
