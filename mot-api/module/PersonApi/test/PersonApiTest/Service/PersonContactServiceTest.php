<?php

namespace PersonApiTest\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthentication\Identity;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Dto\Person\PersonContactDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Exception\DataValidationException;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\PersonContactType;
use DvsaEntities\Repository\PersonContactRepository;
use OrganisationApi\Service\Mapper\PersonContactMapper;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;
use PersonApi\Service\PersonContactService;
use PersonApi\Service\Validator\PersonalDetailsValidator;
use Zend\Authentication\AuthenticationService;

class PersonContactServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PersonContactRepository */
    private $personContactRepositoryMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|PersonContactMapper */
    private $personContactMapperMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityRepository */
    private $emailRepositoryMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|PersonalDetailsValidator */
    private $personalDetailsValidator;
    /** @var \PHPUnit_Framework_MockObject_MockObject|AuthenticationService */
    private $authenticationServiceMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|AuthorisationService */
    private $authorisationServiceMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Identity */
    private $identityMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    private $emMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|PersonDetailsChangeNotificationHelper */
    private $notificationHelperMock;

    /** @var MethodSpy */
    private $persistContactSpy;

    /** @var ContactDetail */
    private $contact;

    public function setUp()
    {
        $this->personContactRepositoryMock = XMock::of(PersonContactRepository::class);
        $this->personContactMapperMock = XMock::of(PersonContactMapper::class, ['toDto']);
        $this->emailRepositoryMock = XMock::of(EntityRepository::class);
        $this->personalDetailsValidator = new PersonalDetailsValidator();
        $this->authenticationServiceMock = XMock::of(AuthenticationService::class, ['getIdentity']);
        $this->authorisationServiceMock = XMock::of(AuthorisationService::class, ['assertGranted']);
        $this->identityMock = XMock::of(Identity::class, ['getUserId']);
        $this->emMock = XMock::of(EntityManager::class);
        $this->notificationHelperMock = XMock::of(PersonDetailsChangeNotificationHelper::class);

        $this->contact = new ContactDetail();
        $person = new Person();
        $personContact = new PersonContact($this->contact, new PersonContactType(), new Person());

        $this->personContactRepositoryMock->expects($this->any())->method('getHydratedByTypeCode')->willReturn($personContact);

        $this->authenticationServiceMock->expects($this->any())->method('getIdentity')->willReturn(new Identity($person));
        $this->emMock->expects($this->any())->method('find')->willReturn($person);

        $this->persistContactSpy = new MethodSpy($this->emMock, 'persist');
    }

    private function createService()
    {
        return new PersonContactService(
            $this->personContactRepositoryMock,
            $this->personContactMapperMock,
            $this->emailRepositoryMock,
            $this->personalDetailsValidator,
            $this->authenticationServiceMock,
            $this->authorisationServiceMock,
            $this->emMock,
            $this->notificationHelperMock
        );
    }

    public function testUpdateEmailForPersonIdReturnsDto()
    {
        $personId = 1;
        $this->authenticationServiceMock->expects($this->atLeastOnce())
            ->method('getIdentity')
            ->willReturn($this->identityMock);

        $contactMock = XMock::of(PersonContact::class, ['getDetails']);
        $dtoMock = XMock::of(PersonContactDto::class);

        $this->personContactRepositoryMock->expects($this->once())
            ->method('getHydratedByTypeCode')
            ->willReturn($contactMock);
        $this->personContactMapperMock->expects($this->once())
            ->method('toDto')
            ->willReturn($dtoMock);
        $service = $this->createService();
        $response = $service->updateEmailForPerson($personId, ['email' => 'sample@email.com']);
        $this->assertEquals($dtoMock, $response);
    }

    public function testupdateEmailForPersonIdWithNoDataThrowsException()
    {
        $personId = 1;
        $this->authenticationServiceMock->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->identityMock);
        $this->setExpectedException(DataValidationException::class);
        $service = $this->createService();
        $service->updateEmailForPerson($personId, []);
    }

    public function testUpdateEmailForPersonIdWithInvalidDataThrowsException()
    {
        $personId = 1;
        $this->authenticationServiceMock->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->identityMock);
        $this->setExpectedException(DataValidationException::class);
        $service = $this->createService();
        $service->updateEmailForPerson($personId, ['email' => 'foo@bar']);
    }

    public function testUpdateEmailForPersonIdWithDifferentUserIdThrowsExceptionWithNoPermission()
    {
        $personId = 2;
        $this->authenticationServiceMock->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->identityMock);
        $this->authorisationServiceMock->expects($this->once())
            ->method('assertGranted')
            ->will($this->throwException(new UnauthorisedException('Not allowed')));
        $this->setExpectedException(UnauthorisedException::class);
        $service = $this->createService();
        $service->updateEmailForPerson($personId, ['email' => 'personcontactservicetest@' . EmailAddressValidator::TEST_DOMAIN]);
    }

    public function testPrimaryEmailIsCreatedWhenTheOnlyExistingEmailsAreNonPrimary()
    {
        // GIVEN I have non primary emails
        $this->contact->addEmail((new Email())->setIsPrimary(false));
        $this->contact->addEmail((new Email())->setIsPrimary(false));

        // WHEN I update my email
        $this->createService()->updateEmailForPerson(1, ['email' => 'proper@email.com']);

        // THEN an email persisted
        $this->assertEquals(1, $this->persistContactSpy->invocationCount());

        /** @var Email $savedEmail */
        $savedEmail = $this->persistContactSpy->paramsForLastInvocation()[0];
        $this->assertTrue($savedEmail->getIsPrimary());
    }

    public function testExcessivePrimaryEmailsAreSetToNonPrimary()
    {
        // GIVEN I have many primary emails
        $email1 = new Email();
        $email2 = new Email();
        $email3 = new Email();
        $this->contact->addEmail($email1->setIsPrimary(true));
        $this->contact->addEmail($email2->setIsPrimary(true));
        $this->contact->addEmail($email3->setIsPrimary(true));

        // WHEN I update my email
        $this->createService()->updateEmailForPerson(1, ['email' => 'proper@email.com']);

        // THEN the first email is updated
        $this->assertEquals('proper@email.com', $email1->getEmail());

        // AND it's still primary
        $this->assertTrue($email1->getIsPrimary());

        // AND the other emails are non primary
        $this->assertFalse($email2->getIsPrimary());
        $this->assertFalse($email3->getIsPrimary());
    }
}
