<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use PHPUnit_Framework_TestCase;

/**
 * Class AuthorisationForAuthorisedExaminerTest.
 */
class AuthorisationForAuthorisedExaminerTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $authorisedExaminer = new AuthorisationForAuthorisedExaminer();

        $this->assertNull($authorisedExaminer->getId(), '"id" should initially be null');
        $this->assertNull($authorisedExaminer->getOrganisation(), '"organisation" should initially be null');
    }

    public function testSetsPropertiesCorrectly()
    {
        $authorisedExaminer = new AuthorisationForAuthorisedExaminer();

        $organisationId = 1;
        $authorisedExaminer->setOrganisation($organisationId);

        $this->assertEquals($organisationId, $authorisedExaminer->getOrganisation());
    }
}
