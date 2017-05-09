<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * VehicleTestingStationDetails.
 *
 * @ORM\Table(
 * name="application_site_details",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 * indexes={
 *  @ORM\Index(name="fk_vts_details_address_id", columns={"address_id"})
 * }
 * )
 * @ORM\Entity
 */
class VehicleTestingStationDetails
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var \DvsaEntities\Entity\Address
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Address", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $address;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationVehicleClass",
     * cascade={"persist"},
     * fetch="LAZY",
     * mappedBy="vehicleTestingStationDetails")
     */
    private $vehicleTestingStationVehicleClass;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationTestingFacility",
     * cascade={"persist"},
     * fetch="LAZY",
     * mappedBy="vehicleTestingStationDetails")
     */
    private $vehicleTestingStationTestingFacility;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationEvidenceOfExclusiveUse
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationEvidenceOfExclusiveUse",
     * cascade={"persist"},
     * fetch="LAZY",
     * mappedBy="vehicleTestingStationDetails")
     */
    private $vehicleTestingStationEvidenceOfExclusiveUse;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationPlanningPermission
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationPlanningPermission",
     * cascade={"persist"},
     * fetch="LAZY",
     * mappedBy="vehicleTestingStationDetails")
     */
    private $vehicleTestingStationPlanningPermission;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions",
     * cascade={"persist"},
     * fetch="LAZY",
     * mappedBy="vehicleTestingStationDetails")
     */
    private $vehicleTestingStationPlansAndDimensions;

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationEvidenceOfExclusiveUse
     * $vehicleTestingStationEvidenceOfExclusiveUse
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function setVehicleTestingStationEvidenceOfExclusiveUse($vehicleTestingStationEvidenceOfExclusiveUse)
    {
        $this->vehicleTestingStationEvidenceOfExclusiveUse = $vehicleTestingStationEvidenceOfExclusiveUse;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationEvidenceOfExclusiveUse
     */
    public function getVehicleTestingStationEvidenceOfExclusiveUse()
    {
        return $this->vehicleTestingStationEvidenceOfExclusiveUse;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     * $vehicleTestingStationTestingFacility
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function setVehicleTestingStationTestingFacility($vehicleTestingStationTestingFacility)
    {
        $this->vehicleTestingStationTestingFacility = $vehicleTestingStationTestingFacility;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     */
    public function getVehicleTestingStationTestingFacility()
    {
        return $this->vehicleTestingStationTestingFacility;
    }

    /**
     * @param \DvsaEntities\Entity\Address $address
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $name
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
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
     * @param \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     * $vehicleTestingStationVehicleClass
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function setVehicleTestingStationVehicleClass($vehicleTestingStationVehicleClass)
    {
        $this->vehicleTestingStationVehicleClass = $vehicleTestingStationVehicleClass;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function getVehicleTestingStationVehicleClass()
    {
        return $this->vehicleTestingStationVehicleClass;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationPlanningPermission
     * $vehicleTestingStationPlanningPermission
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function setVehicleTestingStationPlanningPermission($vehicleTestingStationPlanningPermission)
    {
        $this->vehicleTestingStationPlanningPermission = $vehicleTestingStationPlanningPermission;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationPlanningPermission
     */
    public function getVehicleTestingStationPlanningPermission()
    {
        return $this->vehicleTestingStationPlanningPermission;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     * $vehicleTestingStationPlansAndDimensions
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function setVehicleTestingStationPlansAndDimensions($vehicleTestingStationPlansAndDimensions)
    {
        $this->vehicleTestingStationPlansAndDimensions = $vehicleTestingStationPlansAndDimensions;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     */
    public function getVehicleTestingStationPlansAndDimensions()
    {
        return $this->vehicleTestingStationPlansAndDimensions;
    }
}
