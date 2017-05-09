<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Email.
 *
 * @ORM\Table(
 * name="email",
 * uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})},
 * indexes={
 * @ORM\Index(name="fk_email_contact_detail_id", columns={"contact_detail_id"}),
 * @ORM\Index(name="fk_email_person_created_by", columns={"created_by"}),
 * @ORM\Index(name="fk_email_last_person_updated_by", columns={"last_updated_by"}),
 * }
 * )
 *
 * @ORM\Entity
 */
class Email extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var ContactDetail
     *
     * @ORM\ManyToOne(targetEntity="ContactDetail", inversedBy="emails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_detail_id", referencedColumnName="id")
     * })
     */
    private $contact;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     */
    private $isPrimary = '0';

    /**
     * @param ContactDetail $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @param string $email
     *
     * @return Email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param bool $isPrimary
     *
     * @return Email
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\ContactDetail
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }
}
