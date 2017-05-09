<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="vehicle_v5c", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\VehicleV5CRepository")
 */
class VehicleV5C extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     */
    private $vehicleId;

    /**
     * @var string
     *
     * @ORM\Column(name="v5c_ref", type="string", length=11, nullable=false)
     */
    private $v5cRef;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_seen", type="date", nullable=false)
     */
    private $firstSeen;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_seen", type="date", nullable=true)
     */
    private $lastSeen;

    /**
     * @return \DateTime
     */
    public function getFirstSeen()
    {
        return $this->firstSeen;
    }

    /**
     * @param \DateTime $firstSeen
     *
     * @return $this
     */
    public function setFirstSeen($firstSeen)
    {
        $this->firstSeen = $firstSeen;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastSeen()
    {
        return $this->lastSeen;
    }

    /**
     * @param \DateTime $lastSeen
     *
     * @return $this
     */
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * @return string
     */
    public function getV5cRef()
    {
        return $this->v5cRef;
    }

    /**
     * @param string $v5cRef
     *
     * @return $this
     */
    public function setV5cRef($v5cRef)
    {
        $this->v5cRef = $v5cRef;

        return $this;
    }

    /**
     * @return int
     */
    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    /**
     * @param int $vehicleId
     *
     * @return $this
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;

        return $this;
    }
}
