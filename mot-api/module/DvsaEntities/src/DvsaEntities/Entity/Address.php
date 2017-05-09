<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Address.
 *
 * @ORM\Table(
 *      name="address",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="id_UNIQUE",
 *              columns={"id"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_generic_entity_1_idx", columns={"created_by"}),
 *          @ORM\Index(name="fk_generic_entity_2_idx", columns={"last_updated_by"})
 *      }
 * )
 * @ORM\Entity
 */
class Address extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_1", type="string", length=50, nullable=false)
     */
    private $addressLine1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_2", type="string", length=50, nullable=true)
     */
    private $addressLine2;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_3", type="string", length=50, nullable=true)
     */
    private $addressLine3;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_4", type="string", length=50, nullable=true)
     */
    private $addressLine4;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=10, nullable=true)
     */
    private $postcode;

    /**
     * @var string
     *
     * @ORM\Column(name="town", type="string", length=50, nullable=true)
     */
    private $town;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=10, nullable=false)
     */
    private $country;

    /**
     * @param string $addressLine1
     *
     * @return Address
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * @param string $addressLine2
     *
     * @return Address
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * @param string $addressLine3
     *
     * @return Address
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    /**
     * @param string $addressLine4
     *
     * @return Address
     */
    public function setAddressLine4($addressLine4)
    {
        $this->addressLine4 = $addressLine4;

        return $this;
    }

    /**
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param string $postcode
     *
     * @return Address
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @param string $town
     *
     * @return Address
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @return string
     */
    public function getAddressLine4()
    {
        return $this->addressLine4;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }
}
