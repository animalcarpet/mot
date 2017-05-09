<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Notification.
 *
 * @ORM\Table(name="notification", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_notification_1", columns={"notification_template_id"}), @ORM\Index(name="fk_notification_2", columns={"recipient_id"})})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\NotificationRepository")
 */
class Notification extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Notification';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_on", type="datetime", nullable=true)
     */
    private $readOn;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_archived", type="boolean", nullable=false)
     */
    private $isArchived = false;

    /**
     * @var \DvsaEntities\Entity\NotificationTemplate
     *
     * @ORM\ManyToOne(targetEntity="NotificationTemplate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notification_template_id", referencedColumnName="id")
     * })
     */
    private $notificationTemplate;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="recipient_id", referencedColumnName="id")
     * })
     */
    private $recipient;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(
     *  targetEntity="DvsaEntities\Entity\NotificationField",
     *  mappedBy="notification",
     *  fetch="EAGER",
     *  cascade={"persist", "remove"}
     * )
     */
    private $fields;

    /**
     * @var NotificationAction
     *
     * @ORM\OneToOne(
     *  targetEntity="DvsaEntities\Entity\NotificationAction",
     *  mappedBy="notification",
     *  fetch="EAGER"
     * )
     */
    private $action;

    /**
     * true if this notification has got at least one possible action.
     *
     * @return bool
     */
    public function isActionRequired()
    {
        return $this->getNotificationTemplate() && count($this->getNotificationTemplate()->getActions()) > 0;
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    public function isActionValid($action)
    {
        if ($this->isActionRequired()) {
            $possibleActions = $this->getNotificationTemplate()->getActions();

            /** @var $actionObject NotificationAction */
            foreach ($possibleActions as $actionObject) {
                if ($actionObject->getAction()->getAction() === $action) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * true if an action has been taken.
     *
     * @return bool
     */
    public function isActionDone()
    {
        return null !== $this->getAction();
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @throws NotFoundException | BadRequestException
     */
    public function getFieldValue($key)
    {
        $fields = $this->getFields();

        if (count($fields) > 0) {
            /** @var $field NotificationField */
            foreach ($fields as $field) {
                if ($field->getField() === $key) {
                    return $field->getValue();
                }
            }
            throw new NotFoundException(NotificationField::ENTITY_NAME, $key);
        }
        throw new BadRequestException(
            'Expected not empty array, given '.gettype($fields).' count('.count($fields).')',
            BadRequestException::ERROR_CODE_INVALID_DATA
        );
    }

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $fields
     *
     * @return Notification
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param NotificationField $field
     *
     * @return $this
     */
    public function addField(NotificationField $field)
    {
        $this->fields->add($field);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param \DvsaEntities\Entity\NotificationTemplate $notificationTemplate
     *
     * @return Notification
     */
    public function setNotificationTemplate($notificationTemplate)
    {
        $this->notificationTemplate = $notificationTemplate;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\NotificationTemplate
     */
    public function getNotificationTemplate()
    {
        return $this->notificationTemplate;
    }

    /**
     * @param \DateTime $readOn
     *
     * @return Notification
     */
    public function setReadOn($readOn)
    {
        $this->readOn = $readOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReadOn()
    {
        return $this->readOn;
    }

    /**
     * @param \DvsaEntities\Entity\Person $recipient
     *
     * @return Notification
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Person
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return NotificationAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param NotificationAction $action
     *
     * @return Notification
     */
    public function setAction(NotificationAction $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param bool $isArchived
     *
     * @return Notification
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;

        return $this;
    }
}
