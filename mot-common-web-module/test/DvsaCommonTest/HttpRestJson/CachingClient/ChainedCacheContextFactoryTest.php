<?php

namespace DvsaCommonTest\HttpRestJson\CachingClient;

use DvsaCommon\HttpRestJson\CachingClient\CacheContext;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory;
use DvsaCommon\HttpRestJson\CachingClient\ChainedCacheContextFactory;
use DvsaCommon\HttpRestJson\CachingClient\ConditionalCacheContextFactory;

class ChainedCacheContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItIsACacheContextFactory()
    {
        $this->assertInstanceOf(CacheContextFactory::class, new ChainedCacheContextFactory([]));
    }

    public function testItReturnsANotCachingContextByDefault()
    {
        $cacheContextFactory = new ChainedCacheContextFactory([]);

        $this->assertEquals(CacheContext::notCached(), $cacheContextFactory->fromResourcePath('foo'));
    }

    public function testItReturnsACacheContextOfTheFirstFactoryInTheChain()
    {
        $cacheContext = CacheContext::configured('foo', 10);

        $factory1 = $this->createAcceptingFactory('foo', $cacheContext);
        $factory2 = $this->createAcceptingFactory('foo', CacheContext::configured('bar', 20));

        $cacheContextFactory = new ChainedCacheContextFactory([$factory1, $factory2]);

        $this->assertEquals($cacheContext, $cacheContextFactory->fromResourcePath('foo'));
    }

    public function testItReturnsANotCachingContextIfNoneOfFactoriesSupportsTheResourcePath()
    {
        $factory1 = $this->createNonAcceptingFactory();
        $factory2 = $this->createNonAcceptingFactory();

        $cacheContextFactory = new ChainedCacheContextFactory([$factory1, $factory2]);

        $this->assertEquals(CacheContext::notCached(), $cacheContextFactory->fromResourcePath('foo'));
    }

    public function testItReturnsACacheContextOfTheFirstFactoryInTheChainThatSupportsTheResourcePath()
    {
        $cacheContext = CacheContext::configured('foo', 10);

        $factory1 = $this->createNonAcceptingFactory();
        $factory2 = $this->createAcceptingFactory('foo', $cacheContext);

        $cacheContextFactory = new ChainedCacheContextFactory([$factory1, $factory2]);

        $this->assertEquals($cacheContext, $cacheContextFactory->fromResourcePath('foo'));
    }

    // PHP 5.6 expectedException - \PHPUnit_Framework_Error
    // PHP 7 expectedException - \TypeError
    public function testItOnlyAcceptsConditionalContextFactories()
    {
        try {
            /** @var CacheContextFactory $factory */
            $factory = $this->getMockBuilder(CacheContextFactory::class)->getMock();

            new ChainedCacheContextFactory([$factory]);
        }
        catch (\PHPUnit_Framework_Error $e)
        {
            return;
        }
        catch (\TypeError $e)
        {
            return;
        }
        $this->fail("Expected Exception has not been raised.");
    }

    private function createAcceptingFactory($resourcePath, CacheContext $cacheContext)
    {
        $factory = $this->getMockBuilder(ConditionalCacheContextFactory::class)->getMock();

        $factory->expects($this->any())
            ->method('accepts')
            ->willReturn(true);

        $factory->expects($this->any())
            ->method('fromResourcePath')
            ->with($resourcePath)
            ->willReturn($cacheContext);

        return $factory;
    }

    private function createNonAcceptingFactory()
    {
        $factory = $this->getMockBuilder(ConditionalCacheContextFactory::class)->getMock();

        $factory->expects($this->any())
            ->method('accepts')
            ->willReturn(false);

        return $factory;
    }
}