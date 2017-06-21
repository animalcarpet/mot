<?php

namespace DvsaCommonTest\HttpRestJson;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\HttpRestJson\CachingClient;
use DvsaCommon\HttpRestJson\CachingClient\Cache;
use DvsaCommon\HttpRestJson\CachingClient\CacheContext;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory;
use DvsaCommon\HttpRestJson\Client;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class CachingClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CachingClient
     */
    private $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheContextFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheContext;

    public function setUp()
    {
        $this->decoratedClient = $this->getMockBuilder(Client::class)->getMock();
        $this->cache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheContextFactory = $this->getMockBuilder(CacheContextFactory::class)->getMock();
        $this->cacheContext = CacheContext::configured('/foo', 60, []);

        $this->client = new CachingClient(
            $this->decoratedClient,
            $this->cache,
            $this->cacheContextFactory
        );

        $this->cacheContextFactory->expects($this->any())
            ->method('fromResourcePath')
            ->with('foo')
            ->willReturn($this->cacheContext);
    }

    public function testItIsAnHttpClient()
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testItIsEventManagerAware()
    {
        $this->assertInstanceOf(EventManagerAwareInterface::class, $this->client);
    }

    public function testItProxiesTheSetEventManagerCallIfDecoratedClientIsEventManagerAware()
    {
        $em = $this->getMockBuilder(EventManagerInterface::class)->getMock();

        $this->decoratedClient->expects($this->once())
            ->method('setEventManager')
            ->with($em);

        $this->client->setEventManager($em);
    }

    public function testItProxiesTheGetEventManagerCallIfDecoratedClientIsEventManagerAware()
    {
        $em = $this->getMockBuilder(EventManagerInterface::class)->getMock();

        $this->decoratedClient->expects($this->any())
            ->method('getEventManager')
            ->willReturn($em);

        $this->assertSame($em, $this->client->getEventManager());
    }

    public function testItProxiesSetAccessTokenCall()
    {
        $this->decoratedClient->expects($this->once())
            ->method('setAccessToken')
            ->with('foo');

        $this->client->setAccessToken('foo');
    }

    /**
     * @dataProvider provideGetCalls
     */
    public function testItProxiesGetCalls($method)
    {
        $this->decoratedClient->expects($this->any())
            ->method($method)
            ->with('foo')
            ->willReturn('bar');

        $this->assertSame('bar', $this->client->$method('foo'));
    }

    public function provideGetCalls()
    {
        return [
            ['get'],
            ['getPdf'],
            ['getHtml'],
        ];
    }

    public function testItProxiesGetWithParamsCall()
    {
        $this->decoratedClient->expects($this->any())
            ->method('getWithParams')
            ->with('foo', ['debug' => true])
            ->willReturn('bar');

        $this->assertSame('bar', $this->client->getWithParams('foo', ['debug' => true]));
    }

    public function testItProxiesGetWithParamsReturnDtoCall()
    {
        $dto = $this->getMockForAbstractClass(AbstractDataTransferObject::class);
        $this->decoratedClient->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->with('foo', ['debug' => true])
            ->willReturn($dto);

        $this->assertSame($dto, $this->client->getWithParamsReturnDto('foo', ['debug' => true]));
    }

    /**
     * @dataProvider provideUnsafeHttpMethodCalls
     */
    public function testItProxiesUnsafeHttpMethodCalls($method)
    {
        $this->decoratedClient->expects($this->any())
            ->method($method)
            ->with('foo', ['data' => []])
            ->willReturn('bar');

        $this->assertSame('bar', $this->client->$method('foo', ['data' => []]));
    }

    public function provideUnsafeHttpMethodCalls()
    {
        return [
            ['post'],
            ['postJson'],
            ['patch'],
            ['put'],
            ['putJson'],
        ];
    }

    public function testItProxiesDeleteCall()
    {
        $this->decoratedClient->expects($this->any())
            ->method('delete')
            ->with('foo')
            ->willReturn('bar');

        $this->assertSame('bar', $this->client->delete('foo'));
    }

    /**
     * @dataProvider provideGetCalls
     */
    public function testItFetchesResponseFromCache($method)
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->cacheContext)
            ->willReturn('baz');

        $this->decoratedClient->expects($this->never())
            ->method($method);

        $this->assertSame('baz', $this->client->$method('foo'));
    }

    public function testItStoresResponseInTheCache()
    {
        $this->decoratedClient->expects($this->any())
            ->method('get')
            ->with('foo')
            ->willReturn('baz');

        $this->cache->expects($this->once())
            ->method('store')
            ->with($this->cacheContext, 'baz');

        $this->client->get('foo');
    }

    public function testItFetchesGetWithParamsResponseFromCache()
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->cacheContext)
            ->willReturn('baz');

        $this->decoratedClient->expects($this->never())
            ->method('getWithParams');

        $this->assertSame('baz', $this->client->getWithParams('foo', []));
    }

    public function testItStoresGetWithparamsResponseInTheCache()
    {
        $this->decoratedClient->expects($this->any())
            ->method('getWithParams')
            ->with('foo', ['foo' => 1])
            ->willReturn('baz');

        $this->cache->expects($this->once())
            ->method('store')
            ->with($this->cacheContext, 'baz');

        $this->client->getWithParams('foo', ['foo' => 1]);
    }

    public function testItFetchesGetWithParamsDtoResponseFromCache()
    {
        $dto = $this->getMockForAbstractClass(AbstractDataTransferObject::class);
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->cacheContext)
            ->willReturn($dto);

        $this->decoratedClient->expects($this->never())
            ->method('getWithParamsReturnDto');

        $this->assertSame($dto, $this->client->getWithParamsReturnDto('foo', []));
    }

    public function testItStoresGetWithParamsDtoResponseInTheCache()
    {
        $dto = $this->getMockForAbstractClass(AbstractDataTransferObject::class);
        $this->decoratedClient->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->with('foo', ['foo' => 1])
            ->willReturn($dto);

        $this->cache->expects($this->once())
            ->method('store')
            ->with($this->cacheContext, $dto);

        $this->client->getWithParamsReturnDto('foo', ['foo' => 1]);
    }

    /**
     * @dataProvider provideUnsafeHttpMethodCalls
     */
    public function testItInvalidatesCacheOnUnsafeMethodCall($method)
    {
        $this->cache->expects($this->once())
            ->method('invalidate')
            ->with($this->cacheContext);

        $this->client->$method('foo', []);
    }

    public function testItInvalidatesCacheOnDeleteMethodCall()
    {
        $this->cache->expects($this->once())
            ->method('invalidate')
            ->with($this->cacheContext);

        $this->client->delete('foo');
    }
}