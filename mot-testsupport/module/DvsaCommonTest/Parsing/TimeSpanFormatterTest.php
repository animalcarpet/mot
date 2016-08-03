<?php

namespace DvsaCommonTest\Parsing;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Parsing\TimeSpanParser;
use PHPUnit_Framework_TestCase;

class TimeSpanFormatterTest extends PHPUnit_Framework_TestCase
{
    /** @var \DvsaCommon\Parsing\TimeSpanParser */
    private $parser;

    public function setUp()
    {
        $this->parser = new TimeSpanParser();
    }

    public function testToJsonWithDays()
    {
        $this->assertEquals('10.23:14:24', $this->parser->toJsonString(new TimeSpan(10, 23, 14, 24)));
    }

    public function testToJsonWithoutDays()
    {
        $this->assertEquals('23:14:24', $this->parser->toJsonString(new TimeSpan(0, 23, 14, 24)));
    }

    public function testFromJsonWithDays()
    {
        $expected = new TimeSpan(10, 23, 14, 24);

        $actual = $this->parser->fromJsonString('10.23:14:24');

        $this->assertTrue($expected->equals($actual));
    }

    public function testFromJsonWithoutDays()
    {
        $expected = new TimeSpan(0, 23, 14, 24);

        $actual = $this->parser->fromJsonString('23:14:24');

        $this->assertTrue($expected->equals($actual));
    }

    /**
     * @expectedException \DvsaCommon\Parsing\WrongTimeSpanParserException
     */
    public function testToJsonTooManyColons()
    {
        $this->parser->fromJsonString("1:2:3:4");
    }

    /**
     * @expectedException \DvsaCommon\Parsing\WrongTimeSpanParserException
     */
    public function testToJsonTooManyDots()
    {
        $this->parser->fromJsonString("10.5.16:12:13");
    }

    /**
     * @expectedException \DvsaCommon\Parsing\WrongTimeSpanParserException
     */
    public function testToJsonTooFewColons()
    {
        $this->parser->fromJsonString("1:3.10:10");
    }

    /**
     * @expectedException \DvsaCommon\Parsing\WrongTimeSpanParserException
     */
    public function testToJsonColonBeforeDot()
    {
        $this->parser->fromJsonString("1.3:10");
    }
}
