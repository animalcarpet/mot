<?php

namespace OrganisationApi\Model\Operation;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Service\OrganisationNominationNotificationService;

/**
 * Class DirectNominationOperation
 *
 * Assigns a role to person.
 * Nominee does not need to accept the nomination.
 * He/she gets the role immediately.
 *
 * @package OrganisationApi\Model\Operation
 */
class DirectNominationOperation implements NominateOperationInterface
{

    private $entityManager;
    private $nominationVerifier;
    private $organisationNominationService;
    private $eventService;
    private $dateTimeHolder;
    private $organisationNominationEventHelper;

    public function __construct(
        EntityManager $entityManager,
        NominationVerifier $nominationVerifier,
        OrganisationNominationNotificationService $organisationNominationService,
        EventService $eventService,
        DateTimeHolder $dateTimeHolder,
        OrganisationNominationEventHelper $organisationNominationEventHelper
    ) {
        $this->entityManager = $entityManager;
        $this->nominationVerifier = $nominationVerifier;
        $this->organisationNominationService = $organisationNominationService;
        $this->eventService = $eventService;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->organisationNominationEventHelper = $organisationNominationEventHelper;
    }

    /**
     * @param Person                      $nominator
     * @param OrganisationBusinessRoleMap $nomination
     *
     * @return OrganisationBusinessRoleMap
     */
    public function nominate(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        $this->nominationVerifier->verify($nomination);

        return $this->assignRole($nominator, $nomination);
    }

    public function updateNomination(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        if ($nomination->getBusinessRoleStatus()->getCode() != BusinessRoleStatusCode::PENDING) {
            throw BadRequestException::create('Can only update a pending role nomination');
        }

        return $this->assignRole($nominator, $nomination);
    }

    private function assignRole(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        /** @var BusinessRoleStatus $businessRoleStatus */
        $businessRoleStatus = $this->entityManager->getRepository(BusinessRoleStatus::class)->findOneBy(
            [
                'code' => BusinessRoleStatusCode::ACTIVE,
            ]
        );
        $nomination->setBusinessRoleStatus($businessRoleStatus);

        $event = $this->setEvent($nomination);

        $this->entityManager->persist($nomination);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $notificationId = $this->organisationNominationService->sendDirectNominationNotification($nominator, $nomination);

        $notification = $this->entityManager->find(Notification::class, $notificationId);

        $this->organisationNominationEventHelper->create($notification);

        return $nomination;
    }

    private function setEvent(OrganisationBusinessRoleMap $nomination)
    {
        $positionName = $nomination->getOrganisationBusinessRole()->getFullName();
        $organisationId  = $nomination->getOrganisation()->getAuthorisedExaminer()->getNumber();
        $organisationName = $nomination->getOrganisation()->getName();

        $event = $this->eventService->addEvent(
            EventTypeCode::ROLE_ASSOCIATION_CHANGE,
            sprintf(
                EventDescription::ROLE_ASSOCIATION_CHANGE,
                $positionName,
                $organisationId,
                $organisationName
            ),
            $this->dateTimeHolder->getCurrent(true)
        );

        $eventPersonMap = new EventPersonMap();
        $eventPersonMap->setEvent($event)
                       ->setPerson($nomination->getPerson());

        return $eventPersonMap;
    }
}
