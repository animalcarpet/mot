<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ContactDetail.
 *
 * @ORM\Table(
 * name="contact_detail",
 * uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})},
 * indexes={
 * @ORM\Index(name="fk_generic_entity_1_idx", columns={"created_by"}),
 * @ORM\Index(name="fk_generic_entity_2_idx", columns={"last_updated_by"}),
 * @ORM\Index(name="fk_contact_detail_3_idx", columns={"address_id"})
 * }
 * )
 *
 * @ORM\Entity
 */
class ContactDetail extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="for_attention_of", type="string", length=45, nullable=true)
     */
    private $forAttentionOf;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Address", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    private $address = null;

    /**
     * @var \DvsaEntities\Entity\Phone[]
     *
     * @ORM\OneToMany(targetEntity="Phone", mappedBy="contact", fetch="EAGER", cascade={"persist"})
     */
    private $phones;

    /**
     * @var \DvsaEntities\Entity\Email[]
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="contact", fetch="EAGER", cascade={"persist"})
     */
    private $emails;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->emails = new ArrayCollection();
    }

    /**
     * @param Address|null $address
     *
     * @return ContactDetail
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param string $forAttentionOf
     *
     * @return ContactDetail
     */
    public function setForAttentionOf($forAttentionOf)
    {
        $this->forAttentionOf = $forAttentionOf;

        return $this;
    }

    /**
     * @return Address|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getForAttentionOf()
    {
        return $this->forAttentionOf;
    }

    /**
     * @param Phone $phone
     *
     * @return $this
     */
    public function addPhone($phone)
    {
        $phone->setContact($this);

        $this->phones->add($phone);

        return $this;
    }

    /**
     * @param Phone $phone
     *
     * @return $this
     */
    public function removePhone($phone)
    {
        $this->phones->removeElement($phone);

        $phone->setContact($this);

        return $this;
    }

    /**
     * @return Phone[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @return Phone
     */
    public function getPrimaryPhone()
    {
        return ArrayUtils::firstOrNull(
            $this->getPhones(),
            function (Phone $phone) {
                return $phone->getIsPrimary();
            }
        );
    }

    /**
     * @param Email $email
     *
     * @return ContactDetail
     */
    public function addEmail($email)
    {
        $email->setContact($this);

        $this->emails->add($email);

        return $this;
    }

    /**
     * @param Email $email
     *
     * @return ContactDetail
     */
    public function removeEmail($email)
    {
        $email->setContact($this);
        $this->emails->removeElement($email);

        return $this;
    }

    /**
     * @return Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @return Email
     */
    public function getPrimaryEmail()
    {
        return ArrayUtils::firstOrNull(
            $this->getEmails(),
            function (Email $email) {
                return $email->getIsPrimary();
            }
        );
    }
}
