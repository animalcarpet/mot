<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * VehicleTestingStation.
 *
 * @ORM\Table(
 *  name="site_search",
 *  options={"collate"="utf8_general_ci","charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\VehicleTestingStationSearchRepository")
 */
class VehicleTestingStationSearch
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Site", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $vehicleTestingStation;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=100, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="roles", type="string", length=100, nullable=false)
     */
    private $roles;

    /**
     * @var string
     *
     * @ORM\Column(name="search", type="string", length=400, nullable=false)
     */
    private $search;

    /**
     * @param string $roles
     *
     * @return VehicleTestingStationSearch
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string $search
     *
     * @return VehicleTestingStationSearch
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $status
     *
     * @return VehicleTestingStationSearch
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $type
     *
     * @return VehicleTestingStationSearch
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \DvsaEntities\Entity\Site $vehicleTestingStation
     *
     * @return VehicleTestingStationSearch
     */
    public function setVehicleTestingStation($vehicleTestingStation)
    {
        $this->vehicleTestingStation = $vehicleTestingStation;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Site
     */
    public function getVehicleTestingStation()
    {
        return $this->vehicleTestingStation;
    }
}
