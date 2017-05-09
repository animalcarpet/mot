<?php

namespace DvsaMotTest\Model;

use PHPUnit_Framework_TestCase;

/**
 * Class SlotDetailsTest.
 */
class SlotDetailsTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $slotDetails = new SlotDetails(70, 20, 50);

        $this->assertGreaterThanOrEqual(
            0,
            $slotDetails->getSlots(),
            '"slots" should initially be a value'
        );

        $this->assertGreaterThanOrEqual(
            0,
            $slotDetails->getSlotsInUse(),
            '"slotsInUse" should initially be a value'
        );

        $this->assertGreaterThanOrEqual(
            0,
            $slotDetails->getSlotsWarning(),
            '"slotsWarning" should initially be a value'
        );
    }
}
