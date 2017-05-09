<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Licence.
 *
 * @ORM\Table(name="licence")
 * @ORM\Entity
 */
class Licence extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="licence_number", type="string", length=45, nullable=false)
     */
    private $licenceNumber;

    /**
     * @var LicenceCountry
     *
     * @ORM\ManyToOne(targetEntity="LicenceCountry")
     * @ORM\JoinColumn(name="licence_country_id", referencedColumnName="id", nullable=true)
     */
    private $country;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @var LicenceType
     *
     * @ORM\ManyToOne(targetEntity="LicenceType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="licence_type_id", referencedColumnName="id")
     * })
     */
    private $licenceType;

    /**
     * @param LicenceCountry $country
     *
     * @return Licence
     */
    public function setCountry(LicenceCountry $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param \DateTime $expiryDate
     *
     * @return $this
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * @param string $licenceNumber
     *
     * @return Licence
     */
    public function setLicenceNumber($licenceNumber)
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    /**
     * @param LicenceType $licenceType
     *
     * @return $this
     */
    public function setLicenceType($licenceType)
    {
        $this->licenceType = $licenceType;

        return $this;
    }

    /**
     * @param \DateTime $validFrom
     *
     * @return $this
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country->getName();
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country->getCode();
    }

    /**
     * @return bool
     */
    public function hasCountry()
    {
        return null !== $this->country;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return string
     */
    public function getLicenceNumber()
    {
        return $this->licenceNumber;
    }

    /**
     * @return \DvsaEntities\Entity\LicenceType
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }
}
