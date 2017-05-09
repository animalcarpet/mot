<?php

namespace SessionTest\Service;

use Session\Service\SessionFactory;

/**
 * Class SessionFactoryTest.
 */
class SessionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateServiceWithName()
    {
        $sut = new SessionFactory();

        $mockManager = $this->getMock(\Zend\Session\SessionManager::class);
        $mockSL = $this->getMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockSL
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Zend\Session\SessionManager'))
            ->will($this->returnValue($mockManager));

        $container = $sut->createServiceWithName($mockSL, '', 'test');
        $this->assertInstanceOf(\Zend\Session\Container::class, $container);
        $this->assertEquals('test', $container->getName());
    }

    /**
     * @dataProvider provideCanCreateServiceWithName
     *
     * @param $config
     * @param $name
     * @param $expected
     */
    public function testCanCreateServiceWithName($config, $name, $expected)
    {
        $sut = new SessionFactory();

        $mockSL = $this->getMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockSL
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Config'))
            ->will($this->returnValue($config));

        $this->assertEquals($expected, $sut->canCreateServiceWithName($mockSL, '', $name));
    }

    public function provideCanCreateServiceWithName()
    {
        return [
            [[], 'namespace\test', false],
            [['session_namespace_prefixes' => ['othernamespace\\']], 'namespace\test', false],
            [['session_namespace_prefixes' => ['namespace\\']], 'namespace\test', true],
        ];
    }
}
