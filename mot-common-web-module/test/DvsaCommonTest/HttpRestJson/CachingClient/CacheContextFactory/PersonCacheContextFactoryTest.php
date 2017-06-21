<?php

namespace DvsaCommonTest\HttpRestJson\CachingClient\CacheContextFactory;

use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaCommon\HttpRestJson\CachingClient\CacheContext;
use DvsaCommon\HttpRestJson\CachingClient\CacheContextFactory\PersonCacheContextFactory;
use DvsaCommon\HttpRestJson\CachingClient\ConditionalCacheContextFactory;

class PersonCacheContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN = 'abc123';

    /**
     * @var ConditionalCacheContextFactory
     */
    private $cacheContextFactory;

    /**
     * @var int
     */
    private $lifeTime = 60;

    public function setUp()
    {
        $tokenService = $this->getMockBuilder(TokenServiceInterface::class)->getMock();
        $tokenService->expects($this->any())
            ->method('getToken')
            ->willReturn(self::TOKEN);

        $this->cacheContextFactory = new PersonCacheContextFactory($tokenService, $this->lifeTime);
    }

    public function testItIsAConditionalCacheContextFactory()
    {
        $this->assertInstanceOf(ConditionalCacheContextFactory::class, $this->cacheContextFactory);
    }

    public function testItAcceptsPersonResourcePaths()
    {
        $this->assertTrue($this->cacheContextFactory->accepts('person/42'));
    }

    public function testItDoesNotAcceptOtherResourcePaths()
    {
        $this->assertFalse($this->cacheContextFactory->accepts('not-a-person/42'));
    }

    /**
     * @dataProvider provideCacheContextCases
     */
    public function testItCreatesACacheContext($resourcePath, $expectedCacheContext)
    {
        $this->assertEquals($expectedCacheContext, $this->cacheContextFactory->fromResourcePath($resourcePath));
    }

    public function provideCacheContextCases()
    {
        $hashedToken = sha1(self::TOKEN);

        return [
            [
                'foo',
                CacheContext::notCached(),
                'It creates a not cached context by default'
            ],
            [
                'person/42/site-count',
                CacheContext::configured(
                    $hashedToken.'_person/42/site-count',
                    $this->lifeTime,
                    [
                        $hashedToken.'_person/42/site-count',
                        $hashedToken.'_person/42/mot-testing',
                    ]
                ),
                'It configures the person site count cache'
            ],
            [
                'person/42/mot-testing',
                CacheContext::configured(
                    $hashedToken.'_person/42/mot-testing',
                    $this->lifeTime,
                    [
                        $hashedToken.'_person/42/site-count',
                        $hashedToken.'_person/42/mot-testing',
                    ]
                ),
                'It configures the person mot testing cache'
            ],
            [
                'person/42/demo-test-assessment',
                CacheContext::notCached([
                    $hashedToken.'_person/42/site-count',
                    $hashedToken.'_person/42/mot-testing',
                ]),
                'It invalidates the cache on tester\'s qualification update'
            ],
        ];
    }
}