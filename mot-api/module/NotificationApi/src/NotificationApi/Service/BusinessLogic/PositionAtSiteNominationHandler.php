<?php

namespace NotificationApi\Service\BusinessLogic;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationAction;
use DvsaEntities\Entity\NotificationActionLookup;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification as DtoNotification;
use NotificationApi\Service\NotificationService;
use DvsaCommonApi\Service\EntityFinderTrait;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Constants\EventDescription;
use DvsaEntities\Entity\EventPersonMap;
use NotificationApi\Service\Helper\SiteNominationEventHelper;

/**
 * Handles nomination for a tester at site.
 */
class PositionAtSiteNominationHandler extends AbstractNotificationActionHandler
{
    use EntityFinderTrait;

    /**
     * values from database `notification_template_action`.
     */
    const ACCEPTED = 'SITE-NOMINATION-ACCEPTED';
    const REJECTED = 'SITE-NOMINATION-REJECTED';

    /**
     * values from database `notification_action_lookup`.
     */
    const ACTION_ACCEPTED_ID = NotificationActionLookup::SITE_NOMINATION_ACCEPTED;
    const ACTION_REJECTED_ID = NotificationActionLookup::SITE_NOMINATION_REJECTED;

    /**
     * notification fields.
     */
    const SITE_NAME = 'siteName';
    const SITE_NUMBER = 'siteNumber';
    const POSITION_NAME = 'positionName';

    /** @var $notificationService NotificationService */
    protected $notificationService;

    /** @var EventService $eventService */
    protected $eventService;

    /** SiteNominationEventHelper */
    private $siteNominationEventHelper;

    /**
     * $action === self::ACCEPT or self::REJECT.
     *
     * @param EntityManager             $entityManger
     * @param NotificationService       $notificationService
     * @param string                    $action
     * @param SiteNominationEventHelper $siteNominationEventHelper
     */
    public function __construct(
        EventService $eventService,
        EntityManager $entityManger,
        NotificationService $notificationService,
        $action,
        SiteNominationEventHelper $siteNominationEventHelper
    ) {
        $this->eventService = $eventService;
        $this->entityManager = $entityManger;
        $this->notificationService = $notificationService;
        $this->action = $action;
        $this->siteNominationEventHelper = $siteNominationEventHelper;
    }

    /**
     * Updates association status between person and site.
     *
     * @param Notification $notification
     */
    public function proceed(Notification $notification)
    {
        /** @var $sitePosition SiteBusinessRoleMap */
        $sitePosition = $this->findOrThrowException(
            SiteBusinessRoleMap::class,
            $notification->getFieldValue(self::NOMINATION_ID),
            'SiteBusinessRoleMap'
        );

        $this->update($notification, $sitePosition);
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
     * @param Notification        $notification
     * @param SiteBusinessRoleMap $position
     */
    protected function update(Notification $notification, SiteBusinessRoleMap $position)
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
                    $notification->getFieldValue(self::SITE_NAME)
                ),
                new \DateTime()
            );

            $eventPersonMap = new EventPersonMap();
            $eventPersonMap->setEvent($event)
                           ->setPerson($notification->getRecipient());

            $this->entityManager->persist($position);
            $this->entityManager->persist($eventPersonMap);

            $this->siteNominationEventHelper->create($notification);
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
            ->setTemplate(DtoNotification::TEMPLATE_SITE_NOMINATION_DECISION)
            ->addField(self::NOMINEE_NAME, $notification->getRecipient()->getDisplayName())
            ->addField(self::ACTION, $this->getDecision())
            ->addField(self::POSITION_NAME, $notification->getFieldValue(self::POSITION_NAME))
            ->addField(self::SITE_NAME, $notification->getFieldValue(self::SITE_NAME))
            ->addField(self::SITE_NUMBER, $notification->getFieldValue('siteOrOrganisationId'))
            ->toArray();

        $this->notificationService->add($data);
    }
}
