<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Vehicle.
 *
 * @ORM\Table(name="vehicle")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\VehicleRepository")
 */
class Vehicle extends Entity implements VehicleInterface
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Vehicle';

    const VEHICLE_CLASS_1 = VehicleClassCode::CLASS_1;
    const VEHICLE_CLASS_2 = VehicleClassCode::CLASS_2;
    const VEHICLE_CLASS_3 = VehicleClassCode::CLASS_3;
    const VEHICLE_CLASS_4 = VehicleClassCode::CLASS_4;
    const VEHICLE_CLASS_5 = VehicleClassCode::CLASS_5;
    const VEHICLE_CLASS_7 = VehicleClassCode::CLASS_7;

    /**
     * @var string
     *
     * @ORM\Column(name="registration", type="string", length=20, nullable=true)
     */
    private $registration;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_collapsed", type="string", length=20, nullable=true)
     */
    private $registrationCollapsed;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="string", length=30, nullable=true)
     */
    private $vin;

    /**
     * @var string
     *
     * @ORM\Column(name="vin_collapsed", type="string", length=30, nullable=true)
     */
    private $vinCollapsed;

    /**
     * @var ModelDetail
     *
     * @ORM\ManyToOne(targetEntity="ModelDetail", fetch="EAGER")
     * @ORM\JoinColumn(name="model_detail_id", referencedColumnName="id", nullable=true)
     */
    private $modelDetail;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="smallint", length=4, nullable=true)
     */
    private $year;

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
     * @var \DateTime
     *
     * @ORM\Column(name="first_used_date", type="date", nullable=true)
     */
    private $firstUsedDate;

    /**
     * @var Colour
     *
     * @ORM\ManyToOne(targetEntity="Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="primary_colour_id", referencedColumnName="id")
     * })
     */
    private $colour;

    /**
     * @var Colour
     *
     * @ORM\ManyToOne(targetEntity="Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secondary_colour_id", referencedColumnName="id")
     * })
     */
    private $secondaryColour;

    /**
     * VSI weight for brake tests.
     *
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private $weight;

    /**
     * @var WeightSource
     *
     * @ORM\ManyToOne(targetEntity="WeightSource")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="weight_source_id", referencedColumnName="id")
     * })
     */
    private $weightSource;

    /**
     * @var CountryOfRegistration
     *
     * @ORM\ManyToOne(targetEntity="CountryOfRegistration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_of_registration_id", referencedColumnName="id")
     * })
     */
    private $countryOfRegistration;

    /**
     * @var string
     *
     * @ORM\Column(name="engine_number", type="string", length=30, nullable=true)
     */
    private $engineNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="chassis_number", type="string", length=30, nullable=true)
     */
    private $chassisNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_new_at_first_reg", type="boolean", nullable=false)
     */
    private $newAtFirstReg = 0;

    /**
     * Unique DVLA reference.
     *
     * @var int
     *
     * @ORM\Column(name="dvla_vehicle_id", type="integer", length=11, nullable=true)
     */
    private $dvlaVehicleId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_damaged", type="boolean", nullable=false)
     */
    private $isDamaged = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_destroyed", type="boolean", nullable=false)
     */
    private $isDestroyed = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_incognito", type="boolean", nullable=false)
     */
    private $isIncognito = 0;

    /**
     * @var EmptyReasonMap
     *
     * @ORM\OneToOne(targetEntity="EmptyReasonMap", mappedBy="vehicleId")
     */
    private $emptyReasons;

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param string $registration
     * @return Vehicle
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationCollapsed()
    {
        return $this->registrationCollapsed;
    }

    /**
     * @param string $registrationCollapsed
     * @return Vehicle
     */
    public function setRegistrationCollapsed($registrationCollapsed)
    {
        $this->registrationCollapsed = $registrationCollapsed;
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
     * @param string $vin
     * @return Vehicle
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
        return $this;
    }

    /**
     * @return string
     */
    public function getVinCollapsed()
    {
        return $this->vinCollapsed;
    }

    /**
     * @param string $vinCollapsed
     * @return Vehicle
     */
    public function setVinCollapsed($vinCollapsed)
    {
        $this->vinCollapsed = $vinCollapsed;
        return $this;
    }

    /**
     * @return ModelDetail
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * @param ModelDetail $modelDetail
     * @return Vehicle
     */
    public function setModelDetail($modelDetail)
    {
        $this->modelDetail = $modelDetail;
        return $this;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     * @return Vehicle
     */
    public function setYear($year)
    {
        $this->year = $year;
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
     * @param \DateTime $manufactureDate
     * @return Vehicle
     */
    public function setManufactureDate($manufactureDate)
    {
        $this->manufactureDate = $manufactureDate;
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
     * @param \DateTime $firstRegistrationDate
     * @return Vehicle
     */
    public function setFirstRegistrationDate($firstRegistrationDate)
    {
        $this->firstRegistrationDate = $firstRegistrationDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFirstUsedDate()
    {
        return $this->firstUsedDate;
    }

    /**
     * @param \DateTime $firstUsedDate
     * @return Vehicle
     */
    public function setFirstUsedDate($firstUsedDate)
    {
        $this->firstUsedDate = $firstUsedDate;
        return $this;
    }

    /**
     * @return Colour
     */
    public function getColour()
    {
        return $this->colour;
    }

    /**
     * @param Colour $colour
     * @return Vehicle
     */
    public function setColour($colour)
    {
        $this->colour = $colour;
        return $this;
    }

    /**
     * @return Colour
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @param Colour $secondaryColour
     * @return Vehicle
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return Vehicle
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return WeightSource
     */
    public function getWeightSource()
    {
        return $this->weightSource;
    }

    /**
     * @param WeightSource $weightSource
     * @return Vehicle
     */
    public function setWeightSource($weightSource)
    {
        $this->weightSource = $weightSource;
        return $this;
    }

    /**
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @param CountryOfRegistration $countryOfRegistration
     * @return Vehicle
     */
    public function setCountryOfRegistration($countryOfRegistration)
    {
        $this->countryOfRegistration = $countryOfRegistration;
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
     * @param string $engineNumber
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
    public function getChassisNumber()
    {
        return $this->chassisNumber;
    }

    /**
     * @param string $chassisNumber
     * @return Vehicle
     */
    public function setChassisNumber($chassisNumber)
    {
        $this->chassisNumber = $chassisNumber;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isNewAtFirstReg()
    {
        return (bool)$this->newAtFirstReg;
    }

    /**
     * @param boolean $newAtFirstReg
     * @return Vehicle
     */
    public function setNewAtFirstReg($newAtFirstReg)
    {
        $this->newAtFirstReg = $newAtFirstReg;
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
     * @param int $dvlaVehicleId
     * @return Vehicle
     */
    public function setDvlaVehicleId($dvlaVehicleId)
    {
        $this->dvlaVehicleId = $dvlaVehicleId;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDamaged()
    {
        return $this->isDamaged;
    }

    /**
     * @param boolean $isDamaged
     * @return Vehicle
     */
    public function setDamaged($isDamaged)
    {
        $this->isDamaged = $isDamaged;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDestroyed()
    {
        return $this->isDestroyed;
    }

    /**
     * @param boolean $isDestroyed
     * @return Vehicle
     */
    public function setDestroyed($isDestroyed)
    {
        $this->isDestroyed = $isDestroyed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIncognito()
    {
        return $this->isIncognito;
    }

    /**
     * @param boolean $isIncognito
     * @return Vehicle
     */
    public function setIncognito($isIncognito)
    {
        $this->isIncognito = $isIncognito;
        return $this;
    }

    /**
     * @return Make|null
     */
    public function getMake()
    {
        return $this->getModelDetail()->getModel()->getMake();
    }

    /**
     * @return Model|null
     */
    public function getModel()
    {
        return $this->getModelDetail()->getModel();
    }

    /**
     * @return bool
     */
    public function isVehicleNewAtFirstRegistration()
    {
        return $this->isNewAtFirstReg();
    }

    /**     
     * @return bool
     */
    public function isDvla()
    {
        return false;
    }

    /**
     * @return EmptyReasonMap
     */
    public function getEmptyReasons()
    {
        return $this->emptyReasons;
    }

    /**
     * @return string
     */
    public function getMakeName()
    {
        if ($this->getModelDetail()->getModel() instanceof Model &&
            $this->getModelDetail()->getModel()->getMake() instanceof Make
        ) {
            return $this->getModelDetail()->getModel()->getMake()->getName();
        }
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        if ($this->getModelDetail()->getModel() instanceof Model) {
            return $this->getModelDetail()->getModel()->getName();
        }
    }

    public function getCylinderCapacity()
    {
        return $this->getModelDetail()->getCylinderCapacity();
    }

    public function getEmptyVrmReason()
    {
        if ($this->getEmptyReasons() instanceof EmptyReasonMap) {
            return $this->getEmptyReasons()->getEmptyVrmReason();
        }
    }

    public function getEmptyVinReason()
    {
        if ($this->getEmptyReasons() instanceof EmptyReasonMap) {
            return $this->getEmptyReasons()->getEmptyVinReason();
        }
    }

    public function getTransmissionType()
    {
        return $this->getModelDetail()->getTransmissionType();
    }

    public function getVehicleClass()
    {
        return $this->getModelDetail()->getVehicleClass();
    }

    public function getBodyType()
    {
        return $this->getModelDetail()->getBodyType();
    }

    public function getFuelType()
    {
        return $this->getModelDetail()->getFuelType();
    }
}
