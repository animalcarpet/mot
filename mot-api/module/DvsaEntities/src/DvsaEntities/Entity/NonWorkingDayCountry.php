<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * NonWorkingDayCountry.
 *
 * @ORM\Table(name="non_working_day_country_lookup")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\NonWorkingDayCountryRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class NonWorkingDayCountry extends Entity
{
    use CommonIdentityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_lookup_id", referencedColumnName="id")
     **/
    private $country;

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     *                       return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
