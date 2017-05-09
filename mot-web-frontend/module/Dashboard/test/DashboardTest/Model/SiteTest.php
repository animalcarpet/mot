<?php

namespace DashboardTest\Model;

use Dashboard\Model\Site;

/**
 * Class SiteTest.
 */
class SiteTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;
    const NAME = 'Test name';
    const SITE_NUMBER = 'VTS00001';

    public function test_getterSetters_shouldBeOk()
    {
        $site = new Site(self::getData());
        $this->assertEquals(self::ID, $site->getId());
        $this->assertEquals(self::NAME, $site->getName());
        $this->assertEquals(self::SITE_NUMBER, $site->getSiteNumber());
        $this->assertCount(0, $site->getPositions());
    }

    public static function getData()
    {
        return [
            'id' => self::ID,
            'name' => self::NAME,
            'siteNumber' => self::SITE_NUMBER,
            'positions' => [],
        ];
    }
}
