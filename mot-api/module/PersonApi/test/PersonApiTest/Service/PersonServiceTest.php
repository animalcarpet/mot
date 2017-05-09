<?php

namespace PersonApiTest\Service;

use Dvsa\OpenAM\OpenAMClient;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Service\TesterService;
use OrganisationApi\Service\Mapper\PersonMapper;
use PersonApi\Service\PersonService;
use Zend\Authentication\AuthenticationService;

/**
 * Unit tests for PersonService.
 */
class PersonServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 1;
    const LOGIN = 'aaa';

    public function testGetPerson()
    {
        $personService = $this->constructPersonServiceWithMocks($this->createMocks([], true));
        $this->assertInternalType('array', $personService->getPerson(self::PERSON_ID));
    }

    public function testGetPersonThrowsUnauthorisedExceptionIfNotAuthorised()
    {
        $service = $this->constructPersonServiceWithMocks($this->createMocks([], false));

        $this->setExpectedException(UnauthorisedException::class);
        $this->assertInternalType('array', $service->getPerson(self::PERSON_ID));
    }

    public function testGetPersonById()
    {
        $service = $this->constructPersonServiceWithMocks($this->createMocks([], true));
        $this->assertInstanceOf(Person::class, $service->getPersonById(self::PERSON_ID));
    }

    public function testGetPersonByIdentifier()
    {
        $service = $this->constructPersonServiceWithMocks($this->createMocks([], true));
        $this->assertInstanceOf(Person::class, $service->getPersonByIdentifier(self::LOGIN));
        $this->assertInternalType('array', $service->getPersonByIdentifier(self::LOGIN, false));
        $this->assertInternalType('array', $service->getPersonByIdentifierArray(self::LOGIN));
    }

    public function testGetPersonByLoginThrowsUnauthorisedExceptionIfNotAuthorised()
    {
        $service = $this->constructPersonServiceWithMocks($this->createMocks([], false));

        $this->setExpectedException(UnauthorisedException::class);
        $this->assertInstanceOf(Person::class, $service->getPersonByIdentifier(self::LOGIN));
        $this->assertInternalType('array', $service->getPersonByIdentifier(self::LOGIN, false));
        $this->assertInternalType('array', $service->getPersonByIdentifierArray(self::LOGIN));
    }

    public function testGetCurrentMotByPersonIdWithNoneInProgress()
    {
        $service = $this->constructPersonServiceWithMocks($this->createMocks([], true));
        $this->assertEquals(null, $service->getCurrentMotTestIdByPersonId(self::PERSON_ID)['inProgressTestNumber']);
    }

    public function testAssertUsernameIsValidAndHasAnEmail()
    {
        $personMock = XMock::of(Person::class);
        $personMock
            ->expects($this->once())
            ->method('getPrimaryEmail')
            ->willReturn('personservicetest@'.EmailAddressValidator::TEST_DOMAIN);
        $personMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(self::PERSON_ID);

        $personRepositoryMock = $this->getMockWithDisabledConstructor(PersonRepository::class);
        $personRepositoryMock
            ->expects($this->once())
            ->method('getByIdentifier')
            ->willReturn($personMock);

        $service = $this->constructPersonServiceWithMocks(
            $this->createMocks(
                [
                    PersonRepository::class => $personRepositoryMock,
                ],
                true
            )
        );

        $this->assertSame(self::PERSON_ID, $service->assertUsernameIsValidAndHasAnEmail(self::LOGIN));
    }

    public function testAssertUsernameIsValidAndHasNotAnEmail()
    {
        $person = XMock::of(Person::class);
        $person
            ->expects($this->once())
            ->method('getPrimaryEmail')
            ->willReturn(null);

        $personRepositoryMock = $this->getMockWithDisabledConstructor(PersonRepository::class);
        $personRepositoryMock
            ->expects($this->once())
            ->method('getByIdentifier')
            ->willReturn($person);

        $service = $this->constructPersonServiceWithMocks(
            $this->createMocks(
                [
                    PersonRepository::class => $personRepositoryMock,
                ],
                true
            )
        );

        $this->assertFalse($service->assertUsernameIsValidAndHasAnEmail(self::LOGIN));
    }

    public function testGetPersonSiteCountAsTester()
    {
        $personRepositoryMock = $this->getMockWithDisabledConstructor(PersonRepository::class);
        $personRepositoryMock
            ->expects($this->once())
            ->method('getSiteCount')
            ->willReturn(1);

        $service = $this->constructPersonServiceWithMocks(
            $this->createMocks(
                [
                    PersonRepository::class => $personRepositoryMock,
                ],
                true
            )
        );

        $this->assertSame(['siteCount' => 1], $service->getPersonSiteCountAsTester(self::PERSON_ID));
    }

    public function testRegeneratePinForPerson()
    {
        $personRepositoryMock = $this->getMockWithDisabledConstructor(PersonRepository::class);
        $personRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->createPersonEntity(self::LOGIN));
        $personRepositoryMock
            ->expects($this->any())
            ->method('save')
            ->willReturn(true);

        $service = $this->constructPersonServiceWithMocks(
            $this->createMocks(
                [
                    PersonRepository::class => $personRepositoryMock,
                ],
                true
            )
        );

        $service->regeneratePinForPerson(self::PERSON_ID);
    }

    /**
     * @param array $mocks
     * @param bool  $authorised
     *
     * @throws \Exception
     *
     * @return array
     */
    private function createMocks(array $mocks = [], $authorised = true)
    {
        $mocks[OpenAMClient::class] = isset($mocks[OpenAMClient::class]) ?
            $mocks[OpenAMClient::class] : XMock::of(OpenAMClient::class);

        $mocks[TesterService::class] = isset($mocks[TesterService::class]) ?
            $mocks[TesterService::class] : XMock::of(TesterService::class);

        $mocks[AuthorisationService::class] = isset($mocks[AuthorisationService::class]) ?
            $mocks[AuthorisationService::class] : XMock::of(AuthorisationService::class);

        if (!$authorised) {
            $mocks[AuthorisationService::class]
                ->expects($this->any())
                ->method('assertGranted')
                ->will($this->throwException(new UnauthorisedException('')));
        }

        $mocks[Person::class] = isset($mocks[Person::class]) ?
            $mocks[Person::class] : $this->createPersonEntity(self::LOGIN);

        $mocks[PersonRepository::class] = isset($mocks[PersonRepository::class]) ?
            $mocks[PersonRepository::class] : $this->createRepositoryMock($mocks[Person::class]);

        $mocks[AuthenticationService::class] = $this
            ->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mocks[AuthenticationService::class]
            ->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($this->createPersonEntity('personservicetest@'.EmailAddressValidator::TEST_DOMAIN)));

        return $mocks;
    }

    /**
     * @param array $mocks
     *
     * @throws \Exception
     *
     * @return PersonService
     */
    private function constructPersonServiceWithMocks(array $mocks)
    {
        return new PersonService(
            $mocks[PersonRepository::class],
            new PersonMapper(),
            $mocks[OpenAMClient::class],
            'realm',
            $mocks[TesterService::class],
            $mocks[AuthorisationService::class],
            $mocks[AuthenticationService::class]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createRepositoryMock(Person $person = null)
    {
        $personRepositoryMock = $this->getMockWithDisabledConstructor(PersonRepository::class);

        $personRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->with(self::PERSON_ID)
            ->will($this->returnValue($person));
        $personRepositoryMock
            ->expects($this->any())
            ->method('getByIdentifier')
            ->with(self::LOGIN)
            ->will($this->returnValue($person));

        return $personRepositoryMock;
    }

    private function createPersonEntity($username)
    {
        $person = (new Person())
            ->setUsername($username)
            ->setDateOfBirth(new \DateTime());

        return $person;
    }
}
