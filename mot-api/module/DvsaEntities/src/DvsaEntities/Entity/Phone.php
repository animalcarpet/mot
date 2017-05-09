<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Phone.
 *
 * @ORM\Table(
 * name="phone",
 * uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})},
 * indexes={
 * @ORM\Index(name="fk_phone_contact_detail_id", columns={"contact_detail_id"}),
 * @ORM\Index(name="fk_phone_person_created_by", columns={"created_by"}),
 * @ORM\Index(name="fk_phone_person_last_updated_by", columns={"last_updated_by"}),
 * @ORM\Index(name="fk_phone_contact_type_id", columns={"phone_contact_type_id"})
 * }
 * )
 *
 * @ORM\Entity
 */
class Phone extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=24, nullable=false)
     */
    private $number;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     */
    private $isPrimary = '0';

    /**
     * @var ContactDetail
     *
     * @ORM\ManyToOne(targetEntity="ContactDetail", inversedBy="phones")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_detail_id", referencedColumnName="id")
     * })
     */
    private $contact;

    /**
     * @var PhoneContactType
     *
     * @ORM\ManyToOne(targetEntity="PhoneContactType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phone_contact_type_id", referencedColumnName="id")
     * })
     */
    private $contactType;

    /**
     * @param ContactDetail $contact
     *
     * @return Phone
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return ContactDetail
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param PhoneContactType $contactType
     *
     * @return Phone
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;

        return $this;
    }

    /**
     * @return PhoneContactType
     */
    public function getContactType()
    {
        return $this->contactType;
    }

    /**
     * @param bool $isPrimary
     *
     * @return Phone
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @param string $number
     *
     * @return Phone
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
}
