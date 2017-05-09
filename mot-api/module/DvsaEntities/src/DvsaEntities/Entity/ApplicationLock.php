<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationLock.
 *
 * @ORM\Table(
 * name="application_lock",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 * indexes={@ORM\Index(name="fk_application_lock_person_id", columns={"person_id"})}
 * )
 *
 * @ORM\Entity
 */
class ApplicationLock
{
    const ENTITY_NAME = 'Application Lock';

    /**
     * @var string
     *
     * @ORM\Id
     *
     * @ORM\Column(name="uuid", type="string", length=36, nullable=false)
     */
    private $uuid;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="locked_on", type="datetime", nullable=false)
     */
    private $lockedOn;

    public function __construct($uuid, Person $user)
    {
        $this->uuid = $uuid;
        $this->user = $user;
        $this->lockedOn = new \DateTime();
    }

    /**
     * @param \DateTime $lockedOn
     */
    public function setLockedOn($lockedOn)
    {
        $this->lockedOn = $lockedOn;
    }

    /**
     * @return \DateTime
     */
    public function getLockedOn()
    {
        return $this->lockedOn;
    }

    /**
     * @param \DvsaEntities\Entity\Person $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \DvsaEntities\Entity\Person
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
