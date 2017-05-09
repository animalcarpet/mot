<?php

namespace OrganisationApiTest\Model\Operation;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use PHPUnit_Framework_TestCase;

class ConditionalNominationOperationTest extends PHPUnit_Framework_TestCase
{
    private $entityManager;

    private $nominationVerifier;

    private $notificationService;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->nominationVerifier = XMock::of(NominationVerifier::class);
        $this->notificationService = XMock::of(OrganisationNominationNotificationService::class);
    }

    public function testNewNominationIsPersistedAndNotificationsSent()
    {
        $person = new Person();
        $nomination = new OrganisationBusinessRoleMap();

        $this
            ->expectNominationToBeSaved($nomination)
            ->expectNotificationToBeSent();

        $this->buildOperation()->nominate($person, $nomination);
    }

    public function testNewNominationIsNotPersistedWhenTheNominationIsNotVerified()
    {
        $person = new Person();
        $nomination = new OrganisationBusinessRoleMap();

        $this
            ->expectNominationToNotBeSaved($nomination)
            ->withVerificationFailure()
            ->setExpectedException(BadRequestException::class);

        $this->buildOperation()->nominate($person, $nomination);
    }

    public function testUpdatedNominationHasNotificationsSent()
    {
        $person = new Person();
        $nomination = new OrganisationBusinessRoleMap();

        $this
            ->expectNominationToNotBeSaved($nomination)
            ->expectNotificationToBeSent();

        $this->buildOperation()->updateNomination($person, $nomination);
    }

    private function expectNominationToBeSaved(OrganisationBusinessRoleMap $nomination)
    {
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($nomination);

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->with($nomination);

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

    private function expectNotificationToBeSent()
    {
        $this->notificationService
            ->expects($this->once())
            ->method('sendConditionalNominationNotification');

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
        return new ConditionalNominationOperation(
            $this->entityManager,
            $this->nominationVerifier,
            $this->notificationService
        );
    }
}
