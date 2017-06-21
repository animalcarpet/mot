<?php

namespace DvsaCommonTest\HttpRestJson\CachingClient;

use Doctrine\Common\Cache\Cache as CacheBackend;
use DvsaCommon\HttpRestJson\CachingClient\Cache;
use DvsaCommon\HttpRestJson\CachingClient\CacheContext;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheBackend;

    public function setUp()
    {
        $this->cacheBackend = $this->getMockBuilder(CacheBackend::class)->getMock();
        $this->cache = new Cache($this->cacheBackend);
    }

    public function testFetchReturnsNullIfContextKeyIsNull()
    {
        $this->assertNull($this->cache->fetch(CacheContext::notCached()));
    }

    public function testFetchReturnsUnserializedContentFromTheBackendIfItIsFoundByKey()
    {
        $this->cacheBackend->expects($this->any())
            ->method('fetch')
            ->with('foo')
            ->willReturn(serialize('bar'));

        $this->assertSame('bar', $this->cache->fetch(CacheContext::configured('foo', 60, [])));
    }

    public function testStoreDoesNotCallTheBackendIfKeyIsNull()
    {
        $this->cacheBackend->expects($this->never())
            ->method('save');

        $this->cache->store(CacheContext::notCached(), 'bar');
    }

    public function testStoreSavesValueInTheBackendIfKeyIsSet()
    {
        $this->cacheBackend->expects($this->once())
            ->method('save')
            ->with('foo', serialize('bar'), 60);

        $this->cache->store(CacheContext::configured('foo', 60, []), 'bar');
    }

    public function testInvalidateInvalidatesAllInvalidationCacheKeys()
    {
        $this->cacheBackend->expects($this->at(0))
            ->method('delete')
            ->with('foo/1');
        $this->cacheBackend->expects($this->at(1))
            ->method('delete')
            ->with('foo/2');

        $this->cache->invalidate(CacheContext::configured('foo', 60, ['foo/1', 'foo/2']));
    }
}