<?php

namespace OrganisationApiTest\Model\Operation;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Service\OrganisationNominationNotificationService;

class DirectNominationOperationTest extends \PHPUnit_Framework_TestCase
{
    private $entityManager;
    private $entityRepository;
    private $nominationVerifier;
    private $organisationNominationService;
    private $eventService;
    private $dateTimeHolder;
    private $organisationNominationEventHelper;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->entityRepository = XMock::of(EntityRepository::class);
        $this->nominationVerifier = XMock::of(NominationVerifier::class);
        $this->organisationNominationService = XMock::of(OrganisationNominationNotificationService::class);
        $this->eventService = XMock::of(EventService::class);
        $this->dateTimeHolder = XMock::of(DateTimeHolder::class);
        $this->organisationNominationEventHelper = XMock::of(OrganisationNominationEventHelper::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->entityRepository);

        $this->entityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn((new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE));
    }

    public function testRoleIsAssignedOnNomination()
    {
        $person = new Person();
        $nomination = $this->getPendingNominationWithBusinessRoleStatusCode(BusinessRoleStatusCode::PENDING);

        $this
            ->withNotification(new Notification())
            ->expectNominationToBeSaved($nomination);

        $nomination = $this->buildOperation()->nominate($person, $nomination);

        $this->assertEquals(BusinessRoleStatusCode::ACTIVE, $nomination->getBusinessRoleStatus()->getCode());
    }

    public function testRoleIsNotAssignedOnNominationWhenTheNominationIsNotVerified()
    {
        $person = new Person();
        $nomination = $this->getPendingNominationWithBusinessRoleStatusCode(BusinessRoleStatusCode::PENDING);

        $this
            ->withNotification(new Notification())
            ->expectNominationToNotBeSaved($nomination)
            ->withVerificationFailure()
            ->setExpectedException(BadRequestException::class);

        $this->buildOperation()->nominate($person, $nomination);
    }

    public function testRoleIsAssignedOnUpdateNomination()
    {
        $person = new Person();
        $nomination = $this->getPendingNominationWithBusinessRoleStatusCode(BusinessRoleStatusCode::PENDING);

        $this
            ->withNotification(new Notification())
            ->expectNominationToBeSaved($nomination);

        $nomination = $this->buildOperation()->updateNomination($person, $nomination);

        $this->assertEquals(BusinessRoleStatusCode::ACTIVE, $nomination->getBusinessRoleStatus()->getCode());
    }

    public function testRoleIsNotAssignedOnUpdateNominationIfRoleIsNotPending()
    {
        $person = new Person();
        $nomination = $this->getPendingNominationWithBusinessRoleStatusCode(BusinessRoleStatusCode::ACTIVE);

        $this
            ->withNotification(new Notification())
            ->setExpectedException(BadRequestException::class);

        $this->buildOperation()->updateNomination($person, $nomination);
    }

    private function getPendingNominationWithBusinessRoleStatusCode($code)
    {
        $organisationBusinessRole = new OrganisationBusinessRole();
        $organisationBusinessRole->setFullName('Role Full Name');

        $authorisedExaminer = new AuthorisationForAuthorisedExaminer();
        $authorisedExaminer->setNumber('AE101110');

        $organisation = new Organisation();
        $organisation->setName('An Organisation');
        $organisation->setAuthorisedExaminer($authorisedExaminer);

        $nomination = new OrganisationBusinessRoleMap();
        $nomination->setOrganisation($organisation);
        $nomination->setOrganisationBusinessRole($organisationBusinessRole);
        $nomination->setBusinessRoleStatus((new BusinessRoleStatus())->setCode($code));

        return $nomination;
    }

    private function expectNominationToBeSaved(OrganisationBusinessRoleMap $nomination)
    {
        $this->entityManager
            ->expects($this->atLeastOnce())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        return $this;
    }

    private function expectNominationToNotBeSaved(OrganisationBusinessRoleMap $nomination)
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist')
            ->with($nomination);

        $this->entityManager
            ->expects($this->never())
            ->method('flush')
            ->with($nomination);

        return $this;
    }

    private function withNotification(Notification $notification)
    {
        $this->entityManager
            ->expects($this->any())
            ->method('find')
            ->willReturn($notification);

        return $this;
    }

    private function withVerificationFailure()
    {
        $this->nominationVerifier
            ->method('verify')
            ->will($this->throwException(new BadRequestException('Error', 0)));

        return $this;
    }

    private function buildOperation()
    {
        return new DirectNominationOperation(
            $this->entityManager,
            $this->nominationVerifier,
            $this->organisationNominationService,
            $this->eventService,
            $this->dateTimeHolder,
            $this->organisationNominationEventHelper
        );
    }
}
