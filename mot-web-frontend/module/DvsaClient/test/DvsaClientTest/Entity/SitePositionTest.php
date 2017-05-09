<?php

namespace DvsaClientTest\Entity;

use DvsaClient\Entity\Person;
use DvsaClient\Entity\SitePosition;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;

/**
 * Class SitePositionTest.
 */
class SitePositionTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;
    const ACTIONED_ON = '2011-01-01 12:00:00';
    const STATUS = BusinessRoleStatusCode::ACTIVE;

    public function testGetters()
    {
        $sitePosition = $this->sitePosition();
        $this->assertEquals(self::ID, $sitePosition->getId());
        $this->assertEquals(self::ACTIONED_ON, $sitePosition->getActionedOn());
        $this->assertEquals(self::STATUS, $sitePosition->getStatus());
        $this->assertNotNull($sitePosition->getRoleCode());
        $this->assertNotNull($sitePosition->getPerson());
    }

    public function testIsPending()
    {
        $this->assertTrue(
            $this->sitePosition()->setStatus(BusinessRoleStatusCode::PENDING)->isPending()
        );
    }

    public function testIsAccepted()
    {
        $this->assertTrue($this->sitePosition()->isActive());
    }

    private function sitePosition()
    {
        $user = new Person();

        return (new SitePosition())
            ->setId(self::ID)
            ->setActionedOn(self::ACTIONED_ON)
            ->setPerson($user)
            ->setRoleCode(SiteBusinessRoleCode::TESTER)
            ->setStatus(self::STATUS);
    }
}
