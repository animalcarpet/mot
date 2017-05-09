<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ApplicationAuthorisedExaminer.
 *
 * @ORM\Table(name="application_authorised_examiner", indexes={@ORM\Index(name="fk_organisation", columns={"organisation_id"})})
 * @ORM\Entity
 */
class ApplicationAuthorisedExaminer
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * Set organisation.
     *
     * @param \DvsaEntities\Entity\Organisation $organisation
     *
     * @return ApplicationAuthorisedExaminer
     */
    public function setOrganisation(\DvsaEntities\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation.
     *
     * @return \DvsaEntities\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }
}
