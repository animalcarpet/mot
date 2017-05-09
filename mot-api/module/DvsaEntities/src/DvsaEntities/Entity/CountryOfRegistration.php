<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * CountryOfRegistration.
 *
 * @ORM\Table(name="country_of_registration_lookup")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\CountryOfRegistrationRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class CountryOfRegistration extends Entity
{
    use CommonIdentityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_lookup_id", referencedColumnName="id")
     **/
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="licensing_copy", type="string", length=5, nullable=false)
     */
    private $licensingCopy;

    /**
     * @param Country $country
     *
     * @return CountryOfRegistration;
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
     * @param string $code
     *
     * @return CountryOfRegistration
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $name
     *
     * @return CountryOfRegistration
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $licensingCopy
     *
     * @return $this
     */
    public function setLicensingCopy($licensingCopy)
    {
        $this->licensingCopy = $licensingCopy;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicensingCopy()
    {
        return $this->licensingCopy;
    }
}
