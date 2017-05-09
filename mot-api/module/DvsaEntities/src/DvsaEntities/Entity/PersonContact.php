<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * PersonContact.
 *
 * @ORM\Table(
 *  name="person_contact_detail_map",
 *  options={
 *      "collate"="utf8_general_ci",
 *      "charset"="utf8",
 *      "engine"="InnoDB"
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\PersonContactRepository")
 */
class PersonContact extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var ContactDetail
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\ContactDetail", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     * })
     */
    private $contactDetail;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Person", fetch="LAZY", inversedBy="contacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * @var PersonContactType
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\PersonContactType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_type_id", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * PersonContact constructor.
     *
     * @param \DvsaEntities\Entity\ContactDetail $contactDetail
     * @param PersonContactType                  $type
     * @param Person                             $person
     */
    public function __construct(ContactDetail $contactDetail, PersonContactType $type, Person $person)
    {
        $this->contactDetail = $contactDetail;
        $this->type = $type;
        $this->person = $person;
    }

    /**
     * @param \DvsaEntities\Entity\ContactDetail $contactDetail
     *
     * @return $this
     */
    public function setDetails(ContactDetail $contactDetail)
    {
        $this->contactDetail = $contactDetail;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\ContactDetail
     */
    public function getDetails()
    {
        return $this->contactDetail;
    }

    /**
     * @return PersonContactType
     */
    public function getType()
    {
        return $this->type;
    }
}
