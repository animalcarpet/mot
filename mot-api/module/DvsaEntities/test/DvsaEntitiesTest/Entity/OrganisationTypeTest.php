<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\OrganisationType;
use PHPUnit_Framework_TestCase;

/**
 * Class OrganisationTypeTest.
 */
class OrganisationTypeTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $organisationType = new OrganisationType();

        $this->assertNull(
            $organisationType->getName(), '"organisationType" should initially be null'
        );
    }

    public function testSetsPropertiesCorrectly()
    {
        $organisationType = new OrganisationType();
        $name = 'company';
        $code = 'c';

        $organisationType->setCode($code);
        $organisationType->setName($name);

        $this->assertEquals($code, $organisationType->getCode());
        $this->assertEquals($name, $organisationType->getName());
    }
}
