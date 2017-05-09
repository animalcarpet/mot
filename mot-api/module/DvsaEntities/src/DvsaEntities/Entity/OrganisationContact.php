<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * OrganisationContact.
 *
 * @ORM\Table(
 *      name="organisation_contact_detail_map",
 *      options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity
 */
class OrganisationContact extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var ContactDetail
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\ContactDetail", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_detail_id", referencedColumnName="id")
     * })
     */
    private $contactDetails;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Organisation", fetch="LAZY", inversedBy="contacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * @var \DvsaEntities\Entity\OrganisationContactType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\OrganisationContactType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_contact_type_id", referencedColumnName="id")
     * })
     */
    private $type;

    public function __construct(
        ContactDetail $contactDetails,
        OrganisationContactType $type
    ) {
        $this->contactDetails = $contactDetails;
        $this->type = $type;
    }

    /**
     * @return ContactDetail
     */
    public function getDetails()
    {
        return $this->contactDetails;
    }

    /**
     * @return \DvsaEntities\Entity\OrganisationContactType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Organisation $organisation
     *
     * @return OrganisationContact
     */
    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }
}
