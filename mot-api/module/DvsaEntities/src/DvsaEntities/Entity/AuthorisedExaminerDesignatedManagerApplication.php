<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\UUIDIdentityTrait;

/**
 * AuthorisedExaminerDesignatedManagerApplication.
 *
 * @ORM\Table(name="application_authorised_examiner_designated_manager_application",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity
 */
class AuthorisedExaminerDesignatedManagerApplication
{
    use UUIDIdentityTrait;

    const ENTITY_NAME = 'Authorised Examiner Designated Manager Application';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date_time", type="datetime", nullable=false)
     */
    private $startDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="application_status", type="string", length=50, nullable=false)
     */
    private $applicationStatus = '';

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Person", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function __construct()
    {
        $this->setApplicationStatus(\DvsaCommon\Constants\ApplicationStatus::IN_PROGRESS);
        $this->setStartDateTime(new \DateTime());
    }

    /**
     * Set startDateTime.
     *
     * @param \DateTime $startDateTime
     *
     * @return AuthorisedExaminerDesignatedManagerApplication
     */
    public function setStartDateTime($startDateTime)
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    /**
     * Get startDateTime.
     *
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * Set applicationStatus.
     *
     * @param string $applicationStatus
     *
     * @return AuthorisedExaminerDesignatedManagerApplication
     */
    public function setApplicationStatus($applicationStatus)
    {
        $this->applicationStatus = $applicationStatus;

        return $this;
    }

    /**
     * Get applicationStatus.
     *
     * @return string
     */
    public function getApplicationStatus()
    {
        return $this->applicationStatus;
    }

    /**
     * @param \DvsaEntities\Entity\Person $user
     *
     * @return AuthorisedExaminerDesignatedManagerApplication
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Person
     */
    public function getUser()
    {
        return $this->user;
    }
}
