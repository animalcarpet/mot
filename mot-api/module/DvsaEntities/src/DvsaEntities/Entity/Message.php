<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MessageRepository")
 */
class Message extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * @var MessageType
     *
     * @ORM\ManyToOne(targetEntity="MessageType", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="message_type_id", referencedColumnName="id")
     * })
     */
    private $messageType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime")
     */
    private $issueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime")
     */
    private $expiryDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_acknowledged", type="boolean")
     */
    private $isAcknowledged = false;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=true, unique=true)
     */
    private $token;

    /**
     * @param MessageType $messageType
     *
     * @return Message
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * @return MessageType
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @param Person $person
     *
     * @return Message
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param \DateTime $issueDate
     *
     * @return Message
     */
    public function setIssueDate(\DateTime $issueDate)
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime $expiryDate
     *
     * @return Message
     */
    public function setExpiryDate(\DateTime $expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @param string $token
     *
     * @return Message
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isAcknowledged()
    {
        return (bool) $this->isAcknowledged;
    }

    /**
     * @param $isAcknowledged
     *
     * @return $this
     */
    public function setIsAcknowledged($isAcknowledged)
    {
        $this->isAcknowledged = $isAcknowledged;

        return $this;
    }
}
