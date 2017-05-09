<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An abstract entity for every entity existing in MOT schema.
 *
 * @ORM\MappedSuperclass
 */
abstract class Entity
{
    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    protected $createdBy;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="created_on", type="datetimemicro", nullable=false)
     */
    protected $createdOn;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="last_updated_by", referencedColumnName="id")
     * })
     */
    protected $lastUpdatedBy;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="last_updated_on", type="datetimemicro", nullable=true)
     */
    protected $lastUpdatedOn;

    /**
     * @var int
     *
     * @ORM\Version @ORM\Column(type="integer", nullable=false)
     */
    protected $version = 1;

    //region getters and setters

    /**
     * @return Person|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param Person $value
     *
     * @return $this
     */
    public function setCreatedBy(Person $value)
    {
        $this->createdBy = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setCreatedOn(\DateTime $date)
    {
        $this->createdOn = $date;

        return $this;
    }

    /**
     * @return int|Person
     */
    public function getLastUpdatedBy()
    {
        return $this->lastUpdatedBy;
    }

    /**
     * @param Person $value
     *
     * @return $this
     */
    public function setLastUpdatedBy(Person $value)
    {
        $this->lastUpdatedBy = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdatedOn()
    {
        return $this->lastUpdatedOn;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setLastUpdatedOn(\DateTime $date)
    {
        $this->lastUpdatedOn = $date;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
    //endregion

    /**
     * Return true if entity was last modified or just created by user of id.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isLastModifiedBy($userId)
    {
        $modifiedBy = $this->lastUpdatedBy ? $this->lastUpdatedBy : $this->createdBy;

        return $modifiedBy === null || $userId === $modifiedBy;
    }

    /**
     * Return date of last amend (created or modified).
     *
     * @return \DateTime
     */
    public function getLastAmendedOn()
    {
        return $this->lastUpdatedOn ?: $this->createdOn;
    }

    /**
     * Return person whom last amend the record (created or modified).
     *
     * @return Person
     */
    public function getLastAmendedBy()
    {
        return $this->lastUpdatedBy ?: $this->createdBy;
    }
}
