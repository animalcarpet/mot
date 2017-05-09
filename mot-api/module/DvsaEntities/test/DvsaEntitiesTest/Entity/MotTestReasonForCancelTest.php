<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\MotTestReasonForCancel;
use PHPUnit_Framework_TestCase;

/**
 * Class MotTestReasonForCancelTest.
 */
class MotTestReasonForCancelTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $motTestReasonForCancel = new MotTestReasonForCancel();
        $this->assertNull($motTestReasonForCancel->getAbandoned());
        $this->assertNull($motTestReasonForCancel->getReason());
    }
}
