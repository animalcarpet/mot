<?php

namespace DvsaMotTest\Helper;

use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Session\Container;

class BrakeTestConfigurationContainerHelperTest extends PHPUnit_Framework_TestCase
{
    protected $sessionContainerMock;
    public function setUp()
    {
        $this->sessionContainerMock = XMock::of(Container::class, ['offsetSet', 'offsetGet']);
    }
    public function testConstructorWorks()
    {
        $helper = new BrakeTestConfigurationContainerHelper($this->sessionContainerMock);
        $this->assertInstanceOf(BrakeTestConfigurationContainerHelper::class, $helper);
    }

    public function testPersistConfigStoresData()
    {
        $data = ['foo' => 'bar'];
        $this->sessionContainerMock->expects($this->once())
            ->method('offsetGet')
            ->willReturn($data);
        $helper = new BrakeTestConfigurationContainerHelper($this->sessionContainerMock);
        $helper->persistConfig($data);
        $container = $helper->getContainer();
        $this->assertEquals($data, $container->offsetGet('brakeTestConfigurationData'));
    }
}
