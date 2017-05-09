<?php

namespace ApplicationTest\View\Helper;

use Application\View\Helper\CamelCaseToReadable;

/**
 * Class CamelCaseToReadableTest.
 */
class CamelCaseToReadableTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyString()
    {
        $helper = new CamelCaseToReadable();
        $this->assertEquals('', $helper(''));
    }

    public function testOnlyNumeric()
    {
        $helper = new CamelCaseToReadable();
        $this->assertEquals('12213123', $helper('12213123'));
    }

    public function testSpecialCharacters()
    {
        $input = '_+-_+@£!@$!#';
        $helper = new CamelCaseToReadable();
        $this->assertEquals($input, $helper($input));
    }

    public function testNormalCases()
    {
        $helper = new CamelCaseToReadable();
        $this->assertEquals('This Is My Text', $helper('ThisIsMyText'));
        $this->assertEquals('This Is My Text', $helper('This Is My Text'));
        $this->assertEquals('This Is My Text', $helper('This IsMyText'));
        $this->assertEquals('This Is My Text', $helper('     ThisIsMyText    '));
    }
}
