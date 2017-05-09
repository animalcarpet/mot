<?php

namespace Core\Service;

use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionStorage;

/**
 * Class SessionServiceTest.
 *
 * @group VM-11506
 */
class SessionServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing Constructor of a SessionService.
     */
    public function testConstructor()
    {
        $container = new Container(SessionService::UNIQUE_KEY);

        $service = new SessionService($container, XMock::of(MapperFactory::class));

        $this->assertInstanceOf(SessionService::class, $service);
    }

    /**
     * @throws \Exception
     */
    public function testDestroy()
    {
        $manager = XMock::of(SessionManager::class);
        $manager->expects($this->once())
            ->method('destroy');

        $container = XMock::of(Container::class);
        $container->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);

        $service = new SessionService($container, XMock::of(MapperFactory::class));
        $service->destroy();
    }

    /**
     * @throws \Exception
     */
    public function testClear()
    {
        $storage = XMock::of(SessionStorage::class);
        $storage->expects($this->once())
            ->method('clear')
            ->willReturn($storage);

        $manager = XMock::of(SessionManager::class);
        $manager->expects($this->once())
            ->method('getStorage')
            ->willReturn($storage);

        $container = XMock::of(Container::class);
        $container->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);

        $service = new SessionService($container, XMock::of(MapperFactory::class));
        $service->clear();
    }

    /**
     * @throws \Exception
     */
    public function testLoadPositive()
    {
        $container = XMock::of(Container::class);
        $container->expects($this->once())
            ->method('offsetExists')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('offsetGet');

        $service = new SessionService($container, XMock::of(MapperFactory::class));
        $service->load('key');
    }

    /**
     * @throws \Exception
     */
    public function testLoadNegative()
    {
        $container = XMock::of(Container::class);
        $container->expects($this->once())
            ->method('offsetExists')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('offsetGet');

        $service = new SessionService($container, XMock::of(MapperFactory::class));
        $service->load('key');
    }

    /**
     * @throws \Exception
     */
    public function testSave()
    {
        $container = XMock::of(Container::class);
        $container->expects($this->once())
            ->method('offsetSet')
            ->with('key', 'value');

        $service = new SessionService($container, XMock::of(MapperFactory::class));
        $service->save('key', 'value');
    }

    /**
     * Test if method is going to return an object type of array.
     */
    public function testToArray()
    {
        $container = new Container(SessionService::UNIQUE_KEY);
        $service = new SessionService($container, XMock::of(MapperFactory::class));

        $this->assertInternalType('array', $service->toArray());
    }
}
