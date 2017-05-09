<?php

namespace DvsaAuthenticationTest;

use DvsaAuthentication\CacheableIdentity;
use DvsaAuthentication\Identity;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use Serializable;

class CacheableIdentityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheableIdentity
     */
    private $identity;

    const EXAMPLE_UUID = '12345';

    const EXAMPLE_TOKEN = 'abc';

    protected function setUp()
    {
        $decoratedIdentity = new Identity($this->getPerson());
        $decoratedIdentity->setToken(self::EXAMPLE_TOKEN);
        $decoratedIdentity->setUuid(self::EXAMPLE_UUID);

        $this->identity = new CacheableIdentity($decoratedIdentity);
    }

    public function testItIsAnIdentity()
    {
        $this->assertInstanceOf(Identity::class, $this->identity);
    }

    public function testItIsSerializable()
    {
        $serialized = serialize($this->identity);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(Serializable::class, $this->identity);
        $this->assertInternalType('string', $serialized);
        $this->assertInstanceOf(Identity::class, $unserialized);
        $this->assertSame(self::EXAMPLE_TOKEN, $unserialized->getToken());
        $this->assertSame(self::EXAMPLE_UUID, $unserialized->getUuid());
        $this->assertSame('tester1', $unserialized->getUsername());
        $this->assertSame('Bob Tester', $unserialized->getDisplayName());
        $this->assertSame(42, $unserialized->getUserId());
        $this->assertTrue($unserialized->isAccountClaimRequired());
        $this->assertTrue($unserialized->isPasswordChangeRequired());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testItDoesNotSerializeThePerson()
    {
        $serialized = serialize($this->identity);
        $unserialized = unserialize($serialized);

        $unserialized->getPerson();
    }

    /**
     * expectedException \BadMethodCallException.
     */
    public function testItReturnsNullOnMalformedSerializedData()
    {
        $identity = new CacheableIdentity(new Identity(new Person()));
        $identity->unserialize(serialize([]));

        $this->assertNull($identity->getUsername());
    }

    public function testItLazyLoadsThePersonIfPersonRepositoryIsAvailable()
    {
        $person = $this->getPerson();
        $personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $personRepository->expects($this->any())
            ->method('get')
            ->with(42)
            ->willReturn($person);

        $serialized = serialize($this->identity);
        $unserialized = unserialize($serialized);

        $unserialized->setPersonRepository($personRepository);

        $this->assertSame($person, $unserialized->getPerson());
    }

    private function getPerson()
    {
        $person = new Person();
        $person->setId(42);
        $person->setUsername('tester1');
        $person->setFirstName('Bob');
        $person->setFamilyName('Tester');
        $person->setAccountClaimRequired(true);
        $person->setPasswordChangeRequired(true);

        return $person;
    }
}
