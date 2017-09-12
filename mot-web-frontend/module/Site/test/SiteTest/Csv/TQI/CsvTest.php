<?php
namespace SiteTest\Csv\TQI;

use Site\Csv\TQI\Csv;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_getFieldThrowsException_whenTryAddFieldToNotExistingColumn()
    {
        $csv = new Csv(2, 2);
        $csv->getField(2, 3);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_getFieldThrowsException_whenTryAddFieldToNotExistingRow()
    {
        $csv = new Csv(2, 2);
        $csv->getField(3, 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_addFieldThrowsException_whenTryAddFieldToNotExistingColumn()
    {
        $csv = new Csv(2, 2);
        $csv->addField(3, 2, "test");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_addFieldThrowsException_whenTryAddFieldToNotExistingRow()
    {
        $csv = new Csv(2, 2);
        $csv->addField(2, 3, "test");
    }

    public function test_toArrayReturnsArray()
    {
        $csv = new Csv(2, 2);
        $csvArray = $csv->toArray();

        $this->assertEquals(2, count($csvArray));
        $this->assertEquals(2, count($csvArray[1]));
    }
}