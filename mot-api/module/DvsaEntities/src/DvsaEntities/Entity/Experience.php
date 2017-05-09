<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Experience.
 *
 * @ORM\Table(name="experience", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_experience_1", columns={"person_id"}), @ORM\Index(name="fk_experience_2", columns={"created_by"}), @ORM\Index(name="fk_experience_3", columns={"last_updated_by"})})
 * @ORM\Entity
 */
class Experience extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Experience';

    /**
     * @var string
     *
     * @ORM\Column(name="employer", type="string", length=100, nullable=false)
     */
    private $employer;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_from", type="datetime", nullable=true)
     */
    private $dateFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_to", type="datetime", nullable=true)
     */
    private $dateTo;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * Set employer.
     *
     * @param string $employer
     *
     * @return Experience
     */
    public function setEmployer($employer)
    {
        $this->employer = $employer;

        return $this;
    }

    /**
     * Get employer.
     *
     * @return string
     */
    public function getEmployer()
    {
        return $this->employer;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Experience
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateFrom.
     *
     * @param \DateTime $dateFrom
     *
     * @return Experience
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * Get dateFrom.
     *
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * Set dateTo.
     *
     * @param \DateTime $dateTo
     *
     * @return Experience
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * Get dateTo.
     *
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return Experience
     */
    public function setPerson(\DvsaEntities\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person.
     *
     * @return \DvsaEntities\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
