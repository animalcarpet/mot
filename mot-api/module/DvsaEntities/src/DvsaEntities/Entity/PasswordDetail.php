<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Notification.
 *
 * @ORM\Table(name="password_detail")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\PasswordDetailRepository")
 */
class PasswordDetail extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_expiry_notification_sent_date", type="datetime", nullable=false)
     */
    private $passwordNotificationSentDate;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * @param \Datetime $datetime
     *
     * @return self
     */
    public function setPasswordNotificationSentDate(\Datetime $datetime)
    {
        $this->passwordNotificationSentDate = $datetime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordNotificationSentDate()
    {
        return $this->passwordNotificationSentDate;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return self
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }
}
