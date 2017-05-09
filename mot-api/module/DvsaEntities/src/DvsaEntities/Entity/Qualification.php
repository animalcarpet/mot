<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Qualification.
 *
 * @ORM\Table(name="qualification", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_qualification_1_idx", columns={"awarded_by_organisation_id"}), @ORM\Index(name="fk_qualification_2_idx", columns={"created_by"}), @ORM\Index(name="fk_qualification_3_idx", columns={"last_updated_by"}), @ORM\Index(name="fk_qualification_4_idx", columns={"qualification_type_id"})})
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class Qualification extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Qualification';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_lookup_id", referencedColumnName="id")
     */
    private $country;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="awarded_by_organisation_id", referencedColumnName="id")
     * })
     */
    private $awardedByOrganisation;

    /**
     * @var \DvsaEntities\Entity\QualificationType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\QualificationType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="qualification_type_id", referencedColumnName="id")
     * })
     */
    private $qualificationType;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Qualification
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Qualification
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
     * @param Country $country
     *
     * @return MotTest
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set awardedByOrganisation.
     *
     * @param \DvsaEntities\Entity\Organisation $awardedByOrganisation
     *
     * @return Qualification
     */
    public function setAwardedByOrganisation(\DvsaEntities\Entity\Organisation $awardedByOrganisation = null)
    {
        $this->awardedByOrganisation = $awardedByOrganisation;

        return $this;
    }

    /**
     * Get awardedByOrganisation.
     *
     * @return \DvsaEntities\Entity\Organisation
     */
    public function getAwardedByOrganisation()
    {
        return $this->awardedByOrganisation;
    }

    /**
     * Set qualificationType.
     *
     * @param \DvsaEntities\Entity\QualificationType $qualificationType
     *
     * @return Qualification
     */
    public function setQualificationType(\DvsaEntities\Entity\QualificationType $qualificationType = null)
    {
        $this->qualificationType = $qualificationType;

        return $this;
    }

    /**
     * Get qualificationType.
     *
     * @return \DvsaEntities\Entity\QualificationType
     */
    public function getQualificationType()
    {
        return $this->qualificationType;
    }
}
