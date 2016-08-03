<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ModelDetail
 *
 * @ORM\Table(name="model_detail")
 * @ORM\Entity
 */
class ModelDetail extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Model
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * })
     */
    private $model;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    private $isVerified;

    /**
     * @var \DvsaEntities\Entity\VehicleClass
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\VehicleClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id")
     * })
     */
    private $vehicleClass;

    /**
     * @var \DvsaEntities\Entity\BodyType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\BodyType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="body_type_id", referencedColumnName="id")
     * })
     */
    private $bodyType;

    /**
     * @var \DvsaEntities\Entity\FuelType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\FuelType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fuel_type_id", referencedColumnName="id")
     * })
     */
    private $fuelType;

    /**
     * @var \DvsaEntities\Entity\WheelplanType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\WheelplanType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="wheelplan_type_id", referencedColumnName="id")
     * })
     */
    private $wheelplanType;

    /**
     * @var \DvsaEntities\Entity\TransmissionType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TransmissionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transmission_type_id", referencedColumnName="id")
     * })
     */
    private $transmissionType;

    /**
     * @var string
     *
     * @ORM\Column(name="eu_classification", type="string", length=2, nullable=true)
     */
    private $euClassification;

    /**
     * @var integer
     *
     * @ORM\Column(name="cylinder_capacity", type="integer", length=10, nullable=true)
     */
    private $cylinderCapacity;

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsVerified()
    {
        return $this->isVerified;
    }

    /**
     * @param boolean $isVerified
     * @return $this
     */
    public function setIsVerified($isVerified)
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * @return VehicleClass
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * @param VehicleClass $vehicleClass
     * @return $this
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;
        return $this;
    }

    /**
     * @return BodyType
     */
    public function getBodyType()
    {
        return $this->bodyType;
    }

    /**
     * @param BodyType $bodyType
     */
    public function setBodyType($bodyType)
    {
        $this->bodyType = $bodyType;
        return $this;
    }

    /**
     * @return FuelType
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param FuelType $fuelType
     * @return $this
     */
    public function setFuelType($fuelType)
    {
        $this->fuelType = $fuelType;
        return $this;
    }

    /**
     * @return WheelplanType
     */
    public function getWheelplanType()
    {
        return $this->wheelplanType;
    }

    /**
     * @param WheelplanType $wheelplanType
     * @return $this
     */
    public function setWheelplanType($wheelplanType)
    {
        $this->wheelplanType = $wheelplanType;
        return $this;
    }

    /**
     * @return TransmissionType
     */
    public function getTransmissionType()
    {
        return $this->transmissionType;
    }

    /**
     * @param TransmissionType $transmissionType
     * @return $this
     */
    public function setTransmissionType($transmissionType)
    {
        $this->transmissionType = $transmissionType;
        return $this;
    }

    /**
     * @return string
     */
    public function getEuClassification()
    {
        return $this->euClassification;
    }

    /**
     * @param string $euClassification
     * @return $this
     */
    public function setEuClassification($euClassification)
    {
        $this->euClassification = $euClassification;
        return $this;
    }

    /**
     * @return int
     */
    public function getCylinderCapacity()
    {
        return $this->cylinderCapacity;
    }

    /**
     * @param int $cylinderCapacity
     * @return $this
     */
    public function setCylinderCapacity($cylinderCapacity)
    {
        $this->cylinderCapacity = $cylinderCapacity;
        return $this;
    }
}
