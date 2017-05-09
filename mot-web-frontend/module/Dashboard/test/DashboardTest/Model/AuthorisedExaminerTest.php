<?php

namespace DashboardTest\Model;

use Dashboard\Model\AuthorisedExaminer;

/**
 * Class AuthorisedExaminerTest.
 */
class AuthorisedExaminerTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;
    const MANAGER_ID = '2';
    const REFERENCE = 'AE00001';
    const NAME = 'Kwik fit';
    const TRADING_AS = 'my company';
    const SLOTS = 1212;
    const WARNINGS_SLOTS = 3;
    const POSITION = 'AEDM';

    public function test_getterSetters_shouldBeOk()
    {
        $ae = new AuthorisedExaminer(self::getData());
        $this->assertEquals($ae->getSiteCount(), count($ae->getSites()));
        $this->assertCount(1, $ae->getSites());
        $this->assertEquals(self::ID, $ae->getId());
        $this->assertEquals(self::MANAGER_ID, $ae->getManagerId());
        $this->assertEquals(self::NAME, $ae->getName());
        $this->assertEquals(self::TRADING_AS, $ae->getTradingAs());
        $this->assertEquals(self::SLOTS, $ae->getSlots());
        $this->assertEquals(self::WARNINGS_SLOTS, $ae->getSlotsWarnings());
        $this->assertEquals(self::POSITION, $ae->getPosition());
        $this->assertEquals(self::REFERENCE, $ae->getReference());
    }

    public static function getData()
    {
        return [
            'id' => self::ID,
            'managerId' => self::MANAGER_ID,
            'reference' => self::REFERENCE,
            'name' => self::NAME,
            'tradingAs' => self::TRADING_AS,
            'slots' => self::SLOTS,
            'slotsWarnings' => self::WARNINGS_SLOTS,
            'position' => self::POSITION,
            'sites' => [
                SiteTest::getData(),
            ],
        ];
    }
}
