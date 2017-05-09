<?php

namespace DvsaAuthenticationTest\IdentityFactory;

use Doctrine\Common\Cache\Cache;
use DvsaAuthentication\CacheableIdentity;
use DvsaAuthentication\Identity;
use DvsaAuthentication\IdentityFactory;
use DvsaAuthentication\IdentityFactory\CacheableIdentityFactory;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;

class CacheableIdentityFactoryTest extends \PHPUnit_Framework_TestCase
{
    const LIFE_TIME = 42;
    const EXAMPLE_USERNAME = 'tester1';
    const EXAMPLE_TOKEN = '1234';
    const EXAMPLE_UUID = 'abc';
    const EXAMPLE_PERSON_ID = 13;

    /**
     * @var CacheableIdentityFactory
     */
    private $identityFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedIdentityFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $personRespository;

    public function setUp()
    {
        $this->decoratedIdentityFactory = $this->getMock(IdentityFactory::class);

        $this->cache = $this->getMock(Cache::class);

        $this->personRespository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityFactory = new CacheableIdentityFactory(
            $this->decoratedIdentityFactory,
            $this->cache,
            $this->personRespository,
            self::LIFE_TIME
        );
    }

    public function testItIsAnIdentityFactory()
    {
        $this->assertInstanceOf(IdentityFactory::class, $this->identityFactory);
    }

    public function testItCallsTheDecoratedFactoryIfIdentityIsNotFoundIncache()
    {
        $this->identityIsNotInCache();
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity());

        $identity = $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);

        $this->assertInstanceOf(CacheableIdentity::class, $identity);
    }

    public function testItReturnsACachedVersionOfIdentity()
    {
        $expectedIdentity = $this->getCacheableIdentity();

        $this->identityIsInCache($expectedIdentity);
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity());

        $identity = $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);

        $this->assertEquals($expectedIdentity->getUserId(), $identity->getUserId());
    }

    public function testItSetsPersonRepositoryAfterFetchingIdentityFromCache()
    {
        $person = new Person();
        $person->setId(self::EXAMPLE_PERSON_ID);
        $expectedIdentity = unserialize(serialize(new CacheableIdentity(new Identity($person))));

        $this->personRespository->expects($this->any())
            ->method('get')
            ->with(self::EXAMPLE_PERSON_ID)
            ->willReturn($person);

        $this->identityIsInCache($expectedIdentity);

        $identity = $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);

        $this->assertSame($expectedIdentity->getUserId(), $identity->getUserId());
        $this->assertSame($person, $identity->getPerson());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testItThrowsRuntimeExceptionIfCacheDidNotReturnACacheableIdentity()
    {
        $this->invalidIdentityIsInCache(serialize('foo'));

        $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);
    }

    public function testItStoresCreatedIdentityInCache()
    {
        $this->identityIsNotInCache();
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity());

        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->getCacheKey(), $this->callback(function ($value) {
                return unserialize($value) instanceof CacheableIdentity;
            }), self::LIFE_TIME);

        $identity = $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);
        $this->assertSame(self::EXAMPLE_TOKEN, $identity->getToken());
        $this->assertSame(self::EXAMPLE_UUID, $identity->getUuid());
    }

    public function testItDoesNotStoreCreatedIdentityInCacheIfAccountClaimIsRequired()
    {
        $this->identityIsNotInCache();
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity(true));

        $this->cache->expects($this->never())->method('save');

        $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);
    }

    public function testItDoesNotStoreCreatedIdentityInCacheIfPasswordChangeIsRequired()
    {
        $this->identityIsNotInCache();
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity(false, true));

        $this->cache->expects($this->never())->method('save');

        $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);
    }

    public function testItPopulatesTheCacheAgainIfCacheDidNotReturnASerializedString()
    {
        $this->invalidIdentityIsInCache('foo');
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity());

        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->getCacheKey(), $this->callback(function ($value) {
                return unserialize($value) instanceof CacheableIdentity;
            }), self::LIFE_TIME);

        $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID, null);
    }

    public function testItUpdatesTheUuidAfterLoadingIdentityFromCache()
    {
        $expectedIdentity = $this->getCacheableIdentity();

        $this->identityIsInCache($expectedIdentity);
        $this->identityIsCreatedByDecoratedFactory($this->getIdentity());

        $identity = $this->identityFactory->create(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, 'new-uuid', null);

        $this->assertSame('new-uuid', $identity->getUuid());
    }

    private function getIdentity($isAccountClaimRequired = false, $isPasswordChangeRequired = false)
    {
        $person = new Person();
        $person->setId(self::EXAMPLE_PERSON_ID);
        $person->setAccountClaimRequired($isAccountClaimRequired);
        $person->setPasswordChangeRequired($isPasswordChangeRequired);

        $identity = new Identity($person);
        $identity->setToken(self::EXAMPLE_TOKEN);
        $identity->setUuid(self::EXAMPLE_UUID);

        return $identity;
    }

    private function getCacheableIdentity($isAccountClaimRequired = false, $isPasswordChangeRequired = false)
    {
        return unserialize(serialize(
            new CacheableIdentity($this->getIdentity($isAccountClaimRequired, $isPasswordChangeRequired))
        ));
    }

    private function identityIsNotInCache()
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->getCacheKey())
            ->willReturn(null);
    }

    private function identityIsInCache($expectedIdentity)
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->getCacheKey())
            ->willReturn(serialize($expectedIdentity));
    }

    private function invalidIdentityIsInCache($value)
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->getCacheKey())
            ->willReturn($value);
    }

    private function identityIsCreatedByDecoratedFactory($identity)
    {
        $this->decoratedIdentityFactory->expects($this->any())
            ->method('create')
            ->with(self::EXAMPLE_USERNAME, self::EXAMPLE_TOKEN, self::EXAMPLE_UUID)
            ->willReturn($identity);
    }

    /**
     * @return string
     */
    private function getCacheKey()
    {
        return sha1(self::EXAMPLE_TOKEN).'_identity';
    }
}
