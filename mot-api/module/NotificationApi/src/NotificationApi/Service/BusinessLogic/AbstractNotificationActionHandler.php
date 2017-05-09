<?php

namespace NotificationApi\Service\BusinessLogic;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationTemplateAction;
use NotificationApi\Service\NotificationService;
use Zend\ServiceManager\ServiceManager;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\Helper\SiteNominationEventHelper;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;

/**
 * Notification action handlers factory.
 */
abstract class AbstractNotificationActionHandler
{
    /**
     * notification field keys.
     */
    const NOMINEE_NAME = 'nomineeName';
    const NOMINATOR_ID = 'nominatorId';
    const ACTION = 'action';
    const POSITION_NAME = 'positionName';
    const NOMINATION_ID = 'nominationId';
    const ROLE = 'role';

    /**
     * notification field values.
     */
    const ACTION_ACCEPTED_FIELD = 'accepted';
    const ACTION_REJECTED_FIELD = 'rejected';

    /**
     * @var string
     */
    protected $action;

    /**
     * Returns action handler.
     *
     * @param string         $action
     * @param ServiceManager $serviceManager
     *
     * @return AbstractNotificationActionHandler
     *
     * @throws NotFoundException
     */
    public static function getInstance($action, ServiceManager $serviceManager = null)
    {
        switch ($action) {
            case PositionAtSiteNominationHandler::ACCEPTED:
            case PositionAtSiteNominationHandler::REJECTED:
                return new PositionAtSiteNominationHandler(
                    $serviceManager->get(EventService::class),
                    $serviceManager->get(EntityManager::class),
                    $serviceManager->get(NotificationService::class),
                    $action,
                    $serviceManager->get(SiteNominationEventHelper::class)
                );

            case PositionInOrganisationNominationHandler::ACCEPTED:
            case PositionInOrganisationNominationHandler::REJECTED:
                return new PositionInOrganisationNominationHandler(
                    $serviceManager->get(EventService::class),
                    $serviceManager->get(EntityManager::class),
                    $serviceManager->get(NotificationService::class),
                    $action,
                    $serviceManager->get(OrganisationNominationEventHelper::class)
                );

            default:
                throw new NotFoundException(NotificationTemplateAction::ENTITY_NAME);
        }
    }

    /**
     * Every handler must implement this method.
     *
     * @param Notification $notification
     */
    abstract public function proceed(Notification $notification);
}
