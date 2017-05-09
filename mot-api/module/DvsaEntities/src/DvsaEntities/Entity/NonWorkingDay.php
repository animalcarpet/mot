<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * NonWorkingDay.
 *
 * @ORM\Table(name="non_working_day_lookup")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\NonWorkingDayRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class NonWorkingDay extends Entity
{
    use CommonIdentityTrait;

    /**
     * @ORM\Column(name="day", type="date", nullable=false)
     *
     * @var \DateTime
     */
    private $day;

    /**
     * @ORM\ManyToOne(targetEntity="NonWorkingDayCountry")
     * @ORM\JoinColumn(name="non_working_day_country_lookup_id", referencedColumnName="id")
     **/
    private $country;

    /**
     * @return \DateTime
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param \DateTime $day
     *
     * @return NonWorkingDay;
     */
    public function setDay(\DateTime $day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return NonWorkingDayCountry
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param NonWorkingDayCountry $country
     *
     * @return NonWorkingDay;
     */
    public function setCountry(NonWorkingDayCountry $country)
    {
        $this->country = $country;

        return $this;
    }
}
