<?php

namespace DvsaAuthenticationTest;

use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaEntities\Entity\Person;

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    const EXAMPLE_UUID = '12345';

    const EXAMPLE_TOKEN = 'abc';

    protected function setUp()
    {
        $this->identity = new Identity($this->getPerson());
        $this->identity->setToken(self::EXAMPLE_TOKEN);
        $this->identity->setUuid(self::EXAMPLE_UUID);
    }

    public function testItExposesTheToken()
    {
        $this->assertSame(self::EXAMPLE_TOKEN, $this->identity->getToken());
    }

    public function testItExposesTheUuid()
    {
        $this->assertSame(self::EXAMPLE_UUID, $this->identity->getUuid());
    }

    public function testItIsAnMotIdentity()
    {
        $this->assertInstanceOf(MotIdentityInterface::class, $this->identity);
        $this->assertSame(42, $this->identity->getUserId());
        $this->assertSame('tester1', $this->identity->getUsername());
        $this->assertTrue($this->identity->isAccountClaimRequired());
        $this->assertTrue($this->identity->isPasswordChangeRequired());
    }

    public function testItExposesThePerson()
    {
        $person = $this->getPerson();

        $identity = new Identity($person);

        $this->assertSame($person, $identity->getPerson());
    }

    private function getPerson()
    {
        $person = new Person();
        $person->setId(42);
        $person->setUsername('tester1');
        $person->setAccountClaimRequired(true);
        $person->setPasswordChangeRequired(true);

        return $person;
    }
}
