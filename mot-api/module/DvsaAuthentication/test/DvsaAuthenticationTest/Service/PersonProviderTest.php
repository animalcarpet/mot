<?php

namespace DvsaAuthenticationTest\Service;

use DvsaAuthentication\Service\PersonProvider;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use Zend\Authentication\AuthenticationService;

class PersonProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersonProvider
     */
    private $userProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $personRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticationProvider;

    protected function setUp()
    {
        $this->personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authenticationProvider = $this->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userProvider = new PersonProvider($this->personRepository, $this->authenticationProvider);
    }

    public function testItGetsAPersonFromTheRepository()
    {
        $identity = $this->getIdentity(13);
        $person = new Person();

        $this->authenticationProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $this->personRepository->expects($this->once())
            ->method('get')
            ->with(13)
            ->willReturn($person);

        $this->assertSame($person, $this->userProvider->getPerson());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testItOnlyWorksWithMotIdentity()
    {
        $identity = new \stdClass();

        $this->authenticationProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $this->userProvider->getPerson();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIdentity($userId)
    {
        $identity = $this->getMock(MotIdentityInterface::class);
        $identity->expects($this->any())
            ->method('getUserId')
            ->willReturn($userId);

        return $identity;
    }
}
