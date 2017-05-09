<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SiteFacility.
 *
 * @ORM\Table(name="site_facility", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteFacilityRepository")
 */
class SiteFacility extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", fetch="EAGER", inversedBy="facilities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $vehicleTestingStation;

    /**
     * @var FacilityType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\FacilityType", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="facility_type_id", referencedColumnName="id")
     * })
     */
    private $facilityType;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @param Site $site
     *
     * @return $this
     */
    public function setVehicleTestingStation(Site $site)
    {
        $this->vehicleTestingStation = $site;

        return $this;
    }

    public function getVehicleTestingStation()
    {
        return $this->vehicleTestingStation;
    }

    /**
     * @param FacilityType $facilityType
     *
     * @return $this
     */
    public function setFacilityType($facilityType)
    {
        $this->facilityType = $facilityType;

        return $this;
    }

    /**
     * @return FacilityType
     */
    public function getFacilityType()
    {
        return $this->facilityType;
    }

    /**
     * @param string $name
     *
     * @return $this
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
}
