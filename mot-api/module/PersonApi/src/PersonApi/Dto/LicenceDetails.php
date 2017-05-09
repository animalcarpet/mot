<?php

namespace PersonApi\Dto;

use DvsaEntities\Entity\Licence;
use DvsaEntities\Entity\LicenceType;
use Zend\Stdlib\Hydrator;

class LicenceDetails
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $licenceNumber;

    /**
     * @var string
     */
    private $licenceCountry;

    /**
     * @var \DateTime
     */
    private $validFrom;

    /**
     * @var \DateTime
     */
    private $expiryDate;

    /**
     * @var LicenceType
     */
    private $licenceType;

    /**
     * @param Licence $licence
     */
    public function __construct(Licence $licence)
    {
        $this->id = $licence->getId();
        $this->licenceNumber = $licence->getLicenceNumber();
        if ($licence->hasCountry()) {
            $this->licenceCountry = $licence->getCountry();
        } else {
            $this->licenceCountry = null;
        }

        $this->validFrom = $licence->getValidFrom();
        $this->expiryDate = $licence->getExpiryDate();
        $this->licenceType = $licence->getLicenceType();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $hydrator = new Hydrator\ClassMethods(false);

        return $hydrator->extract($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicenceNumber()
    {
        return $this->licenceNumber;
    }

    /**
     * @param string $licenceNumber
     *
     * @return $this
     */
    public function setLicenceNumber($licenceNumber)
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
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
    public function getLicenceCountry()
    {
        return $this->licenceCountry;
    }

    /**
     * @param string $licenceCountry
     *
     * @return $this
     */
    public function setLicenceCountry($licenceCountry)
    {
        $this->licenceCountry = $licenceCountry;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
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
     * @return LicenceType
     */
    public function getLicenceType()
    {
        return $this->licenceType;
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
}
