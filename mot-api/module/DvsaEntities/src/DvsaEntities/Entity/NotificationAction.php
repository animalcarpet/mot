<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * NotificationAction.
 *
 * @ORM\Table(name="notification_action_map", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_notification_action_1", columns={"notification_id"}), @ORM\Index(name="fk_notification_action_2", columns={"action_id"})})
 * @ORM\Entity
 */
class NotificationAction extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Notification Action';

    /**
     * @var \DvsaEntities\Entity\Notification
     *
     * @ORM\OneToOne(targetEntity="Notification", inversedBy="action")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     * })
     */
    private $notification;

    /**
     * @var \DvsaEntities\Entity\NotificationActionLookup
     *
     * @ORM\ManyToOne(targetEntity="NotificationActionLookup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     * })
     */
    private $action;

    /**
     * @param \DvsaEntities\Entity\NotificationActionLookup $action
     *
     * @return NotificationAction
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\NotificationActionLookup
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param \DvsaEntities\Entity\Notification $notification
     *
     * @return NotificationAction
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
