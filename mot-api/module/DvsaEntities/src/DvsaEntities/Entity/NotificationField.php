<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * NotificationFields.
 *
 * @ORM\Table(name="notification_field", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_notification_fields_1", columns={"notification_id"})})
 * @ORM\Entity
 */
class NotificationField extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Notification Field';

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=30, nullable=true)
     */
    private $field;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=250, nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Notification", inversedBy="fields", cascade={"persist", "remove"} )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     * })
     */
    private $notification;

    /**
     * @param string $field
     *
     * @return NotificationField
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param \DvsaEntities\Entity\Notification $notification
     *
     * @return NotificationField
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

    /**
     * @param string $value
     *
     * @return NotificationField
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
