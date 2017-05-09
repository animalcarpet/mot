<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\ApplicationStatus;
use PHPUnit_Framework_TestCase;

/**
 * Class ApplicationStatusTest.
 */
class ApplicationStatusTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $status = new ApplicationStatus();
        $this->assertEquals(
            $status->getApplicationStatus(), '', '"applicationStatus" should initially an empty string'
        );
    }
}
