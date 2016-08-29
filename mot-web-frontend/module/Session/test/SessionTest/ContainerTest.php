<?php

namespace SessionTest;

use Zend\Session\Container;

/**
 * Class ContainerTest.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArrayCopy()
    {
        $sut = new Container();
        $sut->foo = 'bar';

        $contents = $sut->getArrayCopy();

        $this->assertInternalType('array', $contents);
        $this->assertArrayHasKey('foo', $contents, "'getArrayCopy' doesn't return exchanged array");
    }

    public function testGetArrayCopyAfterExchangeArray()
    {
        $sut = new Container();
        $sut->exchangeArray(['foo' => 'bar']);

        $contents = $sut->getArrayCopy();

        $this->assertInternalType('array', $contents);
        $this->assertArrayHasKey('foo', $contents, "'getArrayCopy' doesn't return exchanged array");
    }
}
