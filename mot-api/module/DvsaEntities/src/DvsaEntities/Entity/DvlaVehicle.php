<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Vehicle information sent from DVLA.
 *
 * @ORM\Table(name="dvla_vehicle", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\DvlaVehicleRepository")
 */
class DvlaVehicle implements VehicleInterface
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="registration", type="string", length=7, nullable=true)
     */
    private $registration;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="string", length=20, nullable=true)
     */
    private $vin;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_collapsed", type="string", length=20, nullable=true)
     */
    private $registrationCollapsed;

    /**
     * @var string
     *
     * @ORM\Column(name="vin_collapsed", type="string", length=30, nullable=true)
     */
    private $vinCollapsed;

    /**
     * @var string
     *
     * @ORM\Column(name="make_code", type="string", length=5, nullable=false)
     */
    private $makeCode;

    /**
     * @var string
     *
     * @ORM\Column(name="make_in_full", type="string", length=20, nullable=true)
     */
    private $makeInFull;

    /**
     * @var string
     *
     * @ORM\Column(name="model_code", type="string", length=5, nullable=false)
     */
    private $modelCode;

    /**
     * @var string
     *
     * @ORM\Column(name="colour_1_code", type="string", length=1, nullable=true)
     */
    private $primaryColour;

    /**
     * @var string
     *
     * @ORM\Column(name="colour_2_code", type="string", length=1, nullable=true)
     */
    private $secondaryColour;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="manufacture_date", type="datetime", nullable=true)
     */
    private $manufactureDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_registration_date", type="date", nullable=true)
     */
    private $firstRegistrationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="propulsion_code", type="string", length=2, nullable=true)
     */
    private $fuelType;

    /**
     * @var string
     *
     * @ORM\Column(name="body_type_code", type="string", length=5, nullable=false)
     */
    private $bodyType;

    /**
     * @var integer
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=true)
     */
    private $vehicleId;

    /**
     * @var integer
     *
     * @ORM\Column(name="engine_capacity", type="integer", nullable=true)
     */
    private $cylinderCapacity;

    /**
     * Designed gross weight in kilos.
     *
     * @var integer
     *
     * @ORM\Column(name="designed_gross_weight", type="integer", nullable=true)
     */
    private $designedGrossWeight;

    /**
     * Unladen weight in kilos.
     *
     * @var integer
     *
     * @ORM\Column(name="unladen_weight", type="integer", nullable=true)
     */
    private $unladenWeight;

    /**
     * Mass in service weight in kilos.
     *
     * @var integer
     *
     * @ORM\Column(name="mass_in_service_weight", type="integer", nullable=true)
     */
    private $massInServiceWeight;

    /**
     * @var string
     *
     * @ORM\Column(name="engine_number", type="string", nullable=true)
     */
    private $engineNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="seating_capacity", type="integer", nullable=true)
     */
    private $seatingCapacity;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_vehicle_new_at_first_registration", type="smallint", nullable=true)
     */
    private $newAtFirstReg;

    /**
     * @param Make|null
     */
    private $make;

    /**
     * @param Model|null
     */
    private $model;

    /**
     * @var ModelDetail|null
     */
    private $modelDetail = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="recent_v5_document_number", type="integer", nullable=true)
     */
    private $v5DocumentNumber;

    /**
     * Unique DVLA reference.
     *
     * @var int
     *
     * @ORM\Column(name="dvla_vehicle_id", type="integer", length=11, nullable=true)
     */
    private $dvlaVehicleId;

    /**
     * @param string $value body type code
     *
     * @return $this
     */
    public function setBodyType($value)
    {
        $this->bodyType = $value;

        return $this;
    }

    /**
     * @return string body type code
     */
    public function getBodyType()
    {
        return $this->bodyType;
    }

    /**
     * @param \DateTime $value
     *
     * @return DvlaVehicle
     */
    public function setFirstRegistrationDate($value)
    {
        $this->firstRegistrationDate = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFirstRegistrationDate()
    {
        return $this->firstRegistrationDate;
    }


    /**
     * @param Integer $value
     * @return DvlaVehicle
     */
    public function setV5DocumentNumber($value)
    {
        $this->v5DocumentNumber = $value;
        return $this;
    }

    /**
     * @return Integer
     */
    public function getV5DocumentNumber()
    {
        return $this->v5DocumentNumber;
    }

    /**
     * First used date is either derived from DVLA vehicle data (Date of Manufacture and Date of First Registration) at
     * time of test registration or is entered by the NT.
     *
     * @return \DateTime
     */
    public function getFirstUsedDate()
    {
        return $this->isVehicleNewAtFirstRegistration() || !$this->getManufactureDate() ?
            $this->getFirstRegistrationDate() : $this->getManufactureDate();
    }

    /**
     * @param string $value fuel type code
     *
     * @return $this
     */
    public function setFuelType($value)
    {
        $this->fuelType = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param string $makeCode
     *
     * @return DvlaVehicle
     */
    public function setMakeCode($makeCode)
    {
        $this->makeCode = $makeCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMakeCode()
    {
        return $this->makeCode;
    }

    /**
     * @param Make|null $value
     *
     * @return DvlaVehicle
     */
    public function setMake(Make $value = null)
    {
        $this->make = $value;

        return $this;
    }

    /**
     * @return Make|null
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @return string|null
     */
    public function getMakeName()
    {
        return $this->make ? $this->make->getName() : null;
    }

    /**
     * @param string $modelCode
     *
     * @return DvlaVehicle
     */
    public function setModelCode($modelCode)
    {
        $this->modelCode = $modelCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getModelCode()
    {
        return $this->modelCode;
    }

    /**
     * @param string $makeInFull
     *
     * @return DvlaVehicle
     */
    public function setMakeInFull($makeInFull)
    {
        $this->makeInFull = $makeInFull;

        return $this;
    }

    /**
     * @return string
     */
    public function getMakeInFull()
    {
        return $this->makeInFull;
    }

    /**
     * @return string|null
     */
    public function getModelName()
    {
        return $this->model ? $this->model->getName() : null;
    }

    /**
     * @param Model|null $model
     *
     * @return $this
     */
    public function setModel(Model $model = null)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param ModelDetail|null $modelDetail
     *
     * @return $this
     */
    public function setModelDetail(ModelDetail $modelDetail = null)
    {
        $this->modelDetail = $modelDetail;

        return $this;
    }

    /**
     * @return ModelDetail|null
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * @param string $value colour code
     *
     * @return DvlaVehicle
     */
    public function setPrimaryColour($value)
    {
        $this->primaryColour = $value;

        return $this;
    }

    /**
     * @return string colour code
     */
    public function getPrimaryColour()
    {
        return $this->primaryColour;
    }

    /**
     * @param string $value colour code
     *
     * @return DvlaVehicle
     */
    public function setSecondaryColour($value)
    {
        $this->secondaryColour = $value;

        return $this;
    }

    /**
     * @return string colour code
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @param \DateTime $date
     *
     * @return Vehicle
     */
    public function setManufactureDate($date)
    {
        $this->manufactureDate = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getManufactureDate()
    {
        return $this->manufactureDate;
    }

    /**
     * @param string $value
     *
     * @return DvlaVehicle
     */
    public function setRegistration($value)
    {
        $this->registration = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param $value
     *
     * @return DvlaVehicle
     */
    public function setVin($value)
    {
        $this->vin = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @param int $vehicleId
     *
     * @return DvlaVehicle
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;

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
     * @param int $cylinderCapacity
     *
     * @return DvlaVehicle
     */
    public function setCylinderCapacity($cylinderCapacity)
    {
        $this->cylinderCapacity = $cylinderCapacity;

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
     * Set designedGrossWeight.
     *
     * @param integer $designedGrossWeight weight in kilos
     *
     * @return DvlaVehicle
     */
    public function setDesignedGrossWeight($designedGrossWeight)
    {
        $this->designedGrossWeight = $designedGrossWeight;

        return $this;
    }

    /**
     * Get designedGrossWeight.
     *
     * @return integer weight in kilos
     */
    public function getDesignedGrossWeight()
    {
        return $this->designedGrossWeight;
    }

    /**
     * Set unladenWeight.
     *
     * @param integer $unladenWeight weight in kilos
     *
     * @return DvlaVehicle
     */
    public function setUnladenWeight($unladenWeight)
    {
        $this->unladenWeight = $unladenWeight;

        return $this;
    }

    /**
     * Get unladen weight.
     *
     * @return integer weight in kilos
     */
    public function getUnladenWeight()
    {
        return $this->unladenWeight;
    }

    /**
     * Set massInServiceWeight.
     *
     * @param integer $massInServiceWeight in kilos
     *
     * @return DvlaVehicle
     */
    public function setMassInServiceWeight($massInServiceWeight)
    {
        $this->massInServiceWeight = $massInServiceWeight;

        return $this;
    }

    /**
     * Get Mass In Service weight.
     *
     * @return integer weight in kilos
     */
    public function getMassInServiceWeight()
    {
        return $this->massInServiceWeight;
    }


    /**
     * @param string $engineNumber
     *
     * @return Vehicle
     */
    public function setEngineNumber($engineNumber)
    {
        $this->engineNumber = $engineNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getEngineNumber()
    {
        return $this->engineNumber;
    }

    /**
     * @param integer $seatingCapacity
     *
     * @return Vehicle
     */
    public function setSeatingCapacity($seatingCapacity)
    {
        $this->seatingCapacity = $seatingCapacity;

        return $this;
    }

    /**
     * @return integer
     */
    public function getSeatingCapacity()
    {
        return $this->seatingCapacity;
    }

    /**
     * @param integer $value
     *
     * @return Vehicle
     */
    public function setNewAtFirstReg($value)
    {
        $this->newAtFirstReg = (boolean)$value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNewAtFirstReg()
    {
        return $this->newAtFirstReg;
    }

    /**
     * @return bool
     */
    public function isVehicleNewAtFirstRegistration()
    {
        return $this->isNewAtFirstReg();
    }

    /**
     * @return $this
     */
    public function setDvlaVehicleId($dvlaVehicleId)
    {
        $this->dvlaVehicleId = $dvlaVehicleId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDvlaVehicleId()
    {
        return $this->dvlaVehicleId;
    }

    /**
     * Indicate based on instantiation the nature of this vehicles origins.
     *
     * @return bool
     */
    public function isDvla()
    {
        return true;
    }
}
