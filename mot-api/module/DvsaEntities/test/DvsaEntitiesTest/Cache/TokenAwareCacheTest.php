<?php

namespace DvsaEntitiesTest\Cache;

use Doctrine\Common\Cache\Cache;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaEntities\Cache\TokenAwareCache;

class TokenAwareCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $token = 'AQIC5wM2LY4Sfcx7eVI7N7Hs1QQW7j2MpBgm1Td6eFqy-Z8.*AAJTSQACMDEAAlNLABMxNTEwODY0MTM5Nzg2NTk1NTk0*';

    /**
     * @var string
     */
    private $hashedToken;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedCache;

    /**
     * @var ApiTokenAwareCache
     */
    private $cache;

    protected function setUp()
    {
        $this->hashedToken = sha1($this->token);

        $this->tokenService = $this->getMock(TokenServiceInterface::class);
        $this->tokenService->expects($this->any())
            ->method('getToken')
            ->willReturn($this->token);

        $this->decoratedCache = $this->getMock(Cache::class);

        $this->cache = new TokenAwareCache($this->decoratedCache, $this->tokenService);
    }

    public function testItImplementsDoctrineCommonCacheInterface()
    {
        $this->assertInstanceOf(Cache::class, $this->cache);
    }

    public function testFetchPrefixesIdWithTokenHash()
    {
        $this->decoratedCache->expects($this->any())
            ->method('fetch')
            ->with($this->hashedToken.'_foo')
            ->willReturn('bar');

        $this->assertSame('bar', $this->cache->fetch('foo'));
    }

    public function testContainsPrefixesIdWithTokenHash()
    {
        $this->decoratedCache->expects($this->any())
            ->method('contains')
            ->with($this->hashedToken.'_foo')
            ->willReturn(true);

        $this->assertTrue($this->cache->contains('foo'));
    }

    public function testSavePrefixesIdWIthTokenHash()
    {
        $this->decoratedCache->expects($this->once())
            ->method('save')
            ->with($this->hashedToken.'_foo', 'bar', 42)
            ->willReturn(true);

        $this->assertTrue($this->cache->save('foo', 'bar', 42));
    }

    public function testDeletePrefixesIdWithTokenHash()
    {
        $this->decoratedCache->expects($this->once())
            ->method('delete')
            ->with($this->hashedToken.'_foo')
            ->willReturn(true);

        $this->assertTrue($this->cache->delete('foo'));
    }

    public function testItProxiesTheCallToGetStats()
    {
        $this->decoratedCache->expects($this->any())
            ->method('getStats')
            ->willReturn(['bar' => 1]);

        $this->assertSame(['bar' => 1], $this->cache->getStats());
    }
}
