<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use PHPUnit_Framework_TestCase;

/**
 * Class AuthorisationForTestingMotAtSiteStatusTest.
 */
class AuthorisationForTestingMotAtSiteStatusTest extends PHPUnit_Framework_TestCase
{
    const CODE = 'code';
    const NAME = 'name';

    public function testInitialState()
    {
        $entity = new AuthorisationForTestingMotAtSiteStatus();

        $this->assertNull($entity->getCode(), '"id" should initially be null');
        $this->assertNull($entity->getName(), '"organisation" should initially be null');
    }

    public function testSetsPropertiesCorrectly()
    {
        $entity = new AuthorisationForTestingMotAtSiteStatus(self::CODE, self::NAME);

        $this->assertEquals(self::CODE, $entity->getCode());
        $this->assertEquals(self::NAME, $entity->getName());

        $entity = new AuthorisationForTestingMotAtSiteStatus();

        $this->assertInstanceOf(AuthorisationForTestingMotAtSiteStatus::class, $entity->setCode(self::CODE));
        $this->assertInstanceOf(AuthorisationForTestingMotAtSiteStatus::class, $entity->setName(self::NAME));
        $this->assertEquals(self::CODE, $entity->getCode());
        $this->assertEquals(self::NAME, $entity->getName());
    }
}
