<?php

namespace NotificationApi\Service\BusinessLogic;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\EntityFinderTrait;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationActionLookup;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification as DtoNotification;
use NotificationApi\Service\NotificationService;
use DvsaEntities\Entity\NotificationAction;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Constants\EventDescription;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;

/**
 * Handles nomination for a tester in organisation.
 */
class PositionInOrganisationNominationHandler extends AbstractNotificationActionHandler
{
    use EntityFinderTrait;

    /**
     * values from database `notification_template_action`.
     */
    const ACCEPTED = 'ORGANISATION-NOMINATION-ACCEPTED';
    const REJECTED = 'ORGANISATION-NOMINATION-REJECTED';

    /**
     * values from database `notification_action_lookup`.
     */
    const ACTION_ACCEPTED_ID = NotificationActionLookup::ORGANISATION_NOMINATION_ACCEPTED;
    const ACTION_REJECTED_ID = NotificationActionLookup::ORGANISATION_NOMINATION_REJECTED;

    /**
     * notification fields.
     */
    const ORGANISATION_NAME = 'organisationName';
    const ORGANISATION_NUMBER = 'organisationNumber';
    const POSITION_NAME = 'positionName';

    /** @var $notificationService NotificationService */
    protected $notificationService;

    /** @var \DvsaEventApi\Service\EventService $eventService */
    protected $eventService;

    /** @var OrganisationNominationEventHelper */
    private $organisationNominationEventHelper;

    /**
     * $action === self::ACCEPT or self::REJECT.
     *
     * @param EventService                      $eventService
     * @param EntityManager                     $entityManger
     * @param NotificationService               $notificationService
     * @param string                            $action
     * @param OrganisationNominationEventHelper $organisationNominationEventHelper
     */
    public function __construct(
        EventService $eventService,
        EntityManager $entityManger,
        NotificationService $notificationService,
        $action,
        OrganisationNominationEventHelper $organisationNominationEventHelper
    ) {
        $this->eventService = $eventService;
        $this->entityManager = $entityManger;
        $this->notificationService = $notificationService;
        $this->action = $action;
        $this->organisationNominationEventHelper = $organisationNominationEventHelper;
    }

    /**
     * Updates association status between person and site.
     *
     * @param Notification $notification
     *
     * @throws BadRequestException
     */
    public function proceed(Notification $notification)
    {
        /** @var $organisationPosition OrganisationBusinessRoleMap */
        $organisationPosition = $this->findOrThrowException(
            OrganisationBusinessRoleMap::class,
            $notification->getFieldValue(self::NOMINATION_ID),
            'OrganisationBusinessRoleMap'
        );

        $this->update($notification, $organisationPosition);
        $this->sendNotificationToNominator($notification);
    }

    /**
     * @param Notification $notification
     * @param int          $actionLookupId
     */
    protected function updateNotificationStatus(Notification $notification, $actionLookupId)
    {
        $actionEntity = new NotificationAction();
        $actionEntity->setNotification($notification);

        /** @var $actionLookupEntity NotificationActionLookup */
        $actionLookupEntity = $this->entityManager->find(NotificationActionLookup::class, $actionLookupId);
        $notification->setAction($actionEntity->setAction($actionLookupEntity));

        $this->entityManager->persist($actionEntity);
    }

    /**
     * @return string
     */
    protected function getDecision()
    {
        return self::ACCEPTED === $this->action ? self::ACTION_ACCEPTED_FIELD : self::ACTION_REJECTED_FIELD;
    }

    /**
     * Updates notification and nomination status, adds RBAC role (if nomination accepted).
     *
     * @param Notification                $notification
     * @param OrganisationBusinessRoleMap $position
     */
    protected function update(Notification $notification, OrganisationBusinessRoleMap $position)
    {
        if (self::ACCEPTED === $this->action) {
            $this->updateNotificationStatus($notification, self::ACTION_ACCEPTED_ID);
            /** @var BusinessRoleStatus $status */
            $status = $this->entityManager->getRepository(\DvsaEntities\Entity\BusinessRoleStatus::class)->findOneBy(
                ['code' => 'AC']
            );
            $position->setBusinessRoleStatus($status);

            $event = $this->eventService->addEvent(
                EventTypeCode::ROLE_ASSOCIATION_CHANGE,
                sprintf(
                    EventDescription::ROLE_ASSOCIATION_CHANGE,
                    $notification->getFieldValue(self::POSITION_NAME),
                    $notification->getFieldValue('siteOrOrganisationId'),
                    $notification->getFieldValue(self::ORGANISATION_NAME)
                ),
                new \DateTime()
            );

            $eventPersonMap = new EventPersonMap();
            $eventPersonMap->setEvent($event)
                           ->setPerson($notification->getRecipient());

            $this->entityManager->persist($position);
            $this->entityManager->persist($eventPersonMap);

            $this->organisationNominationEventHelper->create($notification);
        } else {
            $this->updateNotificationStatus($notification, self::ACTION_REJECTED_ID);
            $this->entityManager->remove($position);
        }

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    private function sendNotificationToNominator(Notification $notification)
    {
        $data = (new DtoNotification())
            ->setRecipient($notification->getFieldValue(self::NOMINATOR_ID))
            ->setTemplate(DtoNotification::TEMPLATE_ORGANISATION_NOMINATION_DECISION)
            ->addField(self::NOMINEE_NAME, $notification->getRecipient()->getDisplayName())
            ->addField(self::ACTION, $this->getDecision())
            ->addField(self::POSITION_NAME, $notification->getFieldValue(self::POSITION_NAME))
            ->addField(self::ORGANISATION_NAME, $notification->getFieldValue(self::ORGANISATION_NAME))
            ->addField(self::ORGANISATION_NUMBER, $notification->getFieldValue('siteOrOrganisationId'))
            ->toArray();

        $this->notificationService->add($data);
    }
}
