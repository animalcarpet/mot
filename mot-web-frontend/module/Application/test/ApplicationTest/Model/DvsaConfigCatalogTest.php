<?php

namespace ApplicationTest\Model;

use Application\Model\DvsaConfigCatalog;

/**
 * Class DvsaConfigCatalogTest.
 */
class DvsaConfigCatalogTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->catalog = new DvsaConfigCatalog(null);
    }

    public function testClass2ItemsNotTestedCorrectlyPreset()
    {
        $items = $this->catalog->getClass4RfrsNotTested();
        $this->assertTrue(in_array(8566, $items));
    }

    public function testClass4ItemsNotTestedCorrectlyPreset()
    {
        $items = $this->catalog->getClass2RfrsNotTested();
        $this->assertTrue(in_array(1022, $items));
    }

    /** @expectedException \Exception */
    public function testCanEnsureRfrItemNotTestedIsNumeric1()
    {
        $this->catalog->isItemNotTested('hello');
    }

    public function testCanEnsureRfrItemNotTestedIsNumeric2()
    {
        $this->assertFalse($this->catalog->isItemNotTested(null));
    }

    /** @expectedException \Exception */
    public function testCanEnsureRfrItemNotTestedIsNumeric3()
    {
        $this->catalog->isItemNotTested([32, 56]);
    }

    public function testCanIdentifyAnyItemNotTested()
    {
        $this->assertTrue($this->catalog->isItemNotTested(8566));
        $this->assertTrue($this->catalog->isItemNotTested(1022));
    }

    public function testCanRejectAnyNonItemNotTested()
    {
        $this->assertFalse($this->catalog->isItemNotTested(-1));
        $this->assertFalse($this->catalog->isItemNotTested(0));
        $this->assertFalse($this->catalog->isItemNotTested(246.434));
        $this->assertFalse($this->catalog->isItemNotTested(31415));
    }
}
