<?php

namespace DvsaAuthenticationTest\IdentityFactory;

use DvsaAuthentication\Identity;
use DvsaAuthentication\IdentityFactory;
use DvsaAuthentication\IdentityFactory\DoctrineIdentityFactory;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\PersonAuthType;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaFeature\FeatureToggles;

class DoctrineIdentityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IdentityFactory
     */
    private $identityFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $personRepository;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    public function setUp()
    {
        $this->personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->featureToggles = $this->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityFactory = new DoctrineIdentityFactory($this->personRepository, $this->featureToggles);
    }

    public function testItIsAnIdentityFactory()
    {
        $this->assertInstanceOf(IdentityFactory::class, $this->identityFactory);
    }

    public function testItReturnsAnIdentity()
    {
        $person = $this->getPerson('tester1');
        $date = new \DateTime('20001212121212');
        $this->personRepository->expects($this->any())
            ->method('findIdentity')
            ->with('tester1')
            ->willReturn($person);

        $identity = $this->identityFactory->create('tester1', 'abcd', '1234', $date);

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertSame($person, $identity->getPerson());
        $this->assertSame('abcd', $identity->getToken());
        $this->assertSame('1234', $identity->getUuid());
        $this->assertSame($date, $identity->getPasswordExpiryDate());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Person "tester1" not found
     */
    public function testItThrowsAnExceptionIfPersonCannotBeFound()
    {
        $this->personRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn(null);

        $this->identityFactory->create('tester1', 'abcd', '1234', null);
    }

    public function testTwoFactorRequiredIsFalseWhenFeatureToggleOff()
    {
        $person = $this->getPerson('tester1');

        // set user to a 2fa card user so the identity is preset two factor required to true
        $person->setAuthenticationMethod(new AuthenticationMethod(PersonAuthType::CARD));

        $date = new \DateTime('20001212121212');
        $this->personRepository->expects($this->any())
            ->method('findIdentity')
            ->with('tester1')
            ->willReturn($person);

        $this->personRepository->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::TWO_FA)
            ->willReturn(false);

        $identity = $this->identityFactory->create('tester1', 'abcd', '1234', $date);

        $this->assertFalse($identity->isSecondFactorRequired());
    }

    /**
     * @param string $username
     *
     * @return Person
     */
    private function getPerson($username)
    {
        $person = new Person();
        $person->setUsername($username);

        return $person;
    }
}