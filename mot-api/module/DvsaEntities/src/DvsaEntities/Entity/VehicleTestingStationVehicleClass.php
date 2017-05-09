<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * VehicleTestingStationVehicleClass.
 *
 * @ORM\Table(
 * name="application_site_vehicle_class",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 * indexes={
 *  @ORM\Index(name="fk_vts_vehicle_class_vts_details_id", columns={"application_site_details_id"})
 * }
 * )
 * @ORM\Entity
 */
class VehicleTestingStationVehicleClass
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationDetails
     *
     * @ORM\OneToOne
     * (
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationDetails",
     * cascade={"persist"},
     * fetch="LAZY",
     * inversedBy="vehicleTestingStationVehicleClass"
     * )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(
     * name="application_site_details_id",
     * referencedColumnName="id",
     * nullable=false)
     * })
     */
    private $vehicleTestingStationDetails = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="class1_and_class2", type="boolean", nullable=false)
     */
    private $class1AndClass2;

    /**
     * @var bool
     *
     * @ORM\Column(name="class3", type="boolean", nullable=false)
     */
    private $class3;

    /**
     * @var bool
     *
     * @ORM\Column(name="class4", type="boolean", nullable=false)
     */
    private $class4;

    /**
     * @var bool
     *
     * @ORM\Column(name="class4A", type="boolean", nullable=false)
     */
    private $class4A;

    /**
     * @var bool
     *
     * @ORM\Column(name="class5", type="boolean", nullable=false)
     */
    private $class5;

    /**
     * @var bool
     *
     * @ORM\Column(name="class5A", type="boolean", nullable=false)
     */
    private $class5A;

    /**
     * @var bool
     *
     * @ORM\Column(name="class5L", type="boolean", nullable=false)
     */
    private $class5L;

    /**
     * @var bool
     *
     * @ORM\Column(name="class7", type="boolean", nullable=false)
     */
    private $class7;

    /**
     * @param bool $class1AndClass2
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass1AndClass2($class1AndClass2)
    {
        $this->class1AndClass2 = $class1AndClass2;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass1AndClass2()
    {
        return $this->class1AndClass2;
    }

    /**
     * @param bool $class3
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass3($class3)
    {
        $this->class3 = $class3;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass3()
    {
        return $this->class3;
    }

    /**
     * @param bool $class4
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass4($class4)
    {
        $this->class4 = $class4;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass4()
    {
        return $this->class4;
    }

    /**
     * @param bool $class4A
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass4A($class4A)
    {
        $this->class4A = $class4A;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass4A()
    {
        return $this->class4A;
    }

    /**
     * @param bool $class5
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass5($class5)
    {
        $this->class5 = $class5;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass5()
    {
        return $this->class5;
    }

    /**
     * @param bool $class5A
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass5A($class5A)
    {
        $this->class5A = $class5A;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass5A()
    {
        return $this->class5A;
    }

    /**
     * @param bool $class5L
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass5L($class5L)
    {
        $this->class5L = $class5L;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass5L()
    {
        return $this->class5L;
    }

    /**
     * @param bool $class7
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
     */
    public function setClass7($class7)
    {
        $this->class7 = $class7;

        return $this;
    }

    /**
     * @return bool
     */
    public function getClass7()
    {
        return $this->class7;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationDetails $vehicleTestingStationDetails
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationVehicleClass
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
