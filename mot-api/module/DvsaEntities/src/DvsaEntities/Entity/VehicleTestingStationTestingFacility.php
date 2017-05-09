<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * VehicleTestingStationTestingFacility.
 *
 * @ORM\Table(
 * name="application_site_testing_facility",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 * indexes={
 *  @ORM\Index(
 * name="fk_vts_testing_facility_vts_details_id",
 * columns={"application_site_details_id"}
 * )
 * }
 * )
 * @ORM\Entity
 */
class VehicleTestingStationTestingFacility
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationDetails
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationDetails",
     * cascade={"persist"},
     * fetch="LAZY",
     * inversedBy="vehicleTestingStationTestingFacility")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(
     * name="application_site_details_id",
     * referencedColumnName="id",
     * nullable=false
     * )
     * })
     */
    private $vehicleTestingStationDetails = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="petrol_engine", type="boolean", nullable=false)
     */
    private $petrolEngine;

    /**
     * @var bool
     *
     * @ORM\Column(name="diesel_engine", type="boolean", nullable=false)
     */
    private $dieselEngine;

    /**
     * @var bool
     *
     * @ORM\Column(name="automated_test_lane_atl", type="boolean", nullable=false)
     */
    private $automatedTestLaneAtl;

    /**
     * @var bool
     *
     * @ORM\Column(name="one_person_test_lane_optl", type="boolean", nullable=false)
     */
    private $onePersonTestLaneOptl;

    /**
     * @param bool $automatedTestLaneAtl
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     */
    public function setAutomatedTestLaneAtl($automatedTestLaneAtl)
    {
        $this->automatedTestLaneAtl = $automatedTestLaneAtl;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAutomatedTestLaneAtl()
    {
        return $this->automatedTestLaneAtl;
    }

    /**
     * @param bool $dieselEngine
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     */
    public function setDieselEngine($dieselEngine)
    {
        $this->dieselEngine = $dieselEngine;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDieselEngine()
    {
        return $this->dieselEngine;
    }

    /**
     * @param bool $onePersonTestLaneOptl
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     */
    public function setOnePersonTestLaneOptl($onePersonTestLaneOptl)
    {
        $this->onePersonTestLaneOptl = $onePersonTestLaneOptl;

        return $this;
    }

    /**
     * @return bool
     */
    public function getOnePersonTestLaneOptl()
    {
        return $this->onePersonTestLaneOptl;
    }

    /**
     * @param bool $petrolEngine
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     */
    public function setPetrolEngine($petrolEngine)
    {
        $this->petrolEngine = $petrolEngine;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPetrolEngine()
    {
        return $this->petrolEngine;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationDetails $vehicleTestingStationDetails
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationTestingFacility
     */
    public function setVehicleTestingStationDetails($vehicleTestingStationDetails)
    {
        $this->vehicleTestingStationDetails = $vehicleTestingStationDetails;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function getVehicleTestingStationDetails()
    {
        return $this->vehicleTestingStationDetails;
    }
}
