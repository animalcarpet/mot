<?php

namespace DvsaMotTest\Model;

use PHPUnit_Framework_TestCase;

/**
 * Class OdometerUpdateTest.
 */
class OdometerUpdateTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $odometerUpdate = new OdometerUpdate();

        $this->assertNull(
            $odometerUpdate->odometer,
            '"odometer" should initially be null'
        );
    }

    public function testExchangeArraySetsPropertiesCorrectly()
    {
        $odometerUpdate = new OdometerUpdate();
        $data = ['odometer' => '23000'];

        $odometerUpdate->exchangeArray($data);

        $this->assertSame(
            $data['odometer'],
            $odometerUpdate->odometer,
            '"odometer" was not set correctly'
        );
    }
}
