<?php

namespace DvsaCommonTest\DtoSerialization\Conversion;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\Convertion\DtoTimeSpanConverter;
use PHPUnit_Framework_TestCase;

class DtoDateIntervalConverterTest extends PHPUnit_Framework_TestCase
{
    /** @var DtoTimeSpanConverter */
    private $converter;

    public function setUp()
    {
        $this->converter = new DtoTimeSpanConverter();
    }

    public function testReflectorDoesNotReturnPropertiesThatDoNotHaveASetter()
    {
        $timeSpan = new TimeSpan(40, 21, 13, 29);
        $converted = $this->converter->objectToJson($timeSpan);

        $this->assertTrue(is_string($converted), "The converted value should be a string");

        $restored = $this->converter->jsonToObject($converted);
        $this->assertInstanceOf(TimeSpan::class, $restored);

        $this->assertEquals(40, $timeSpan->getDays());
        $this->assertEquals(21, $timeSpan->getHours());
        $this->assertEquals(13, $timeSpan->getMinutes());
        $this->assertEquals(29, $timeSpan->getSeconds());
    }
}
