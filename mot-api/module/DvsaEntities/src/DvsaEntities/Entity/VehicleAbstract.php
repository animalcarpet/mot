<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class VehicleAbstract.
 */
class VehicleAbstract extends Entity implements VehicleInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="chassis_number", type="string", length=30)
     */
    protected $chassisNumber;

    /**
     * @var Colour
     *
     * @ORM\ManyToOne(targetEntity="Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="primary_colour_id", referencedColumnName="id")
     * })
     */
    protected $colour;

    /**
     * @var CountryOfRegistration
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\CountryOfRegistration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_of_registration_id", referencedColumnName="id")
     * })
     */
    protected $countryOfRegistration;

    /**
     * Unique DVLA reference.
     *
     * @var int
     *
     * @ORM\Column(name="dvla_vehicle_id", type="integer", length=11)
     */
    protected $dvla_vehicle_id;

    /**
     * @var string
     *
     * @ORM\Column(name="engine_number", type="string", length=30)
     */
    protected $engineNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_registration_date", type="date")
     */
    protected $firstRegistrationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_used_date", type="date")
     */
    protected $firstUsedDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_damaged", type="boolean")
     */
    protected $isDamaged = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_destroyed", type="boolean")
     */
    protected $isDestroyed = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_incognito", type="boolean")
     */
    protected $isIncognito = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_new_at_first_reg", type="boolean")
     */
    protected $newAtFirstReg = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="manufacture_date", type="datetime")
     */
    protected $manufactureDate;

    /**
     * @var ModelDetail
     *
     * @ORM\ManyToOne(targetEntity="ModelDetail", fetch="EAGER")
     * @ORM\JoinColumn(name="model_detail_id", referencedColumnName="id")
     */
    protected $modelDetail;

    /**
     * @var string
     *
     * @ORM\Column(name="registration", type="string", length=20)
     */
    protected $registration;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_collapsed", type="string", length=20)
     */
    protected $registrationCollapsed;

    /**
     * @var Colour
     *
     * @ORM\ManyToOne(targetEntity="Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secondary_colour_id", referencedColumnName="id")
     * })
     */
    protected $secondaryColour;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="string", length=30)
     */
    protected $vin;

    /**
     * @var string
     *
     * @ORM\Column(name="vin_collapsed", type="string", length=30)
     */
    protected $vinCollapsed;

    /**
     * VSI weight for brake tests.
     *
     * @var int
     *
     * @ORM\Column(name="weight", type="integer")
     */
    protected $weight;

    /**
     * @var WeightSource
     *
     * @ORM\ManyToOne(targetEntity="WeightSource")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="weight_source_id", referencedColumnName="id")
     * })
     */
    protected $weightSource;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="smallint", length=4)
     */
    protected $year;

    /**
     * @return string
     */
    public function getChassisNumber()
    {
        return $this->chassisNumber;
    }

    /**
     * @return Colour
     */
    public function getColour()
    {
        return $this->colour;
    }

    /**
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @return int
     */
    public function getDvlaVehicleId()
    {
        return $this->dvla_vehicle_id;
    }

    /**
     * @return string
     */
    public function getEngineNumber()
    {
        return $this->engineNumber;
    }

    /**
     * @return \DateTime
     */
    public function getFirstRegistrationDate()
    {
        return $this->firstRegistrationDate;
    }

    /**
     * @return \DateTime
     */
    public function getFirstUsedDate()
    {
        return $this->firstUsedDate;
    }

    /**
     * @return bool
     */
    public function isIsDamaged()
    {
        return $this->isDamaged;
    }

    /**
     * @return bool
     */
    public function isIsDestroyed()
    {
        return $this->isDestroyed;
    }

    /**
     * @return bool
     */
    public function isDvla()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isIsIncognito()
    {
        return $this->isIncognito;
    }

    /**
     * @return bool
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
     * @return Make|null
     */
    public function getMake()
    {
        if ($this->getModelDetail() && $this->getModelDetail()->getModel()) {
            return $this->getModelDetail()->getModel()->getMake();
        }
    }

    /**
     * @return \DateTime
     */
    public function getManufactureDate()
    {
        return $this->manufactureDate;
    }

    /**
     * @return Model|null
     */
    public function getModel()
    {
        return $this->getModelDetail()->getModel();
    }

    /**
     * @return ModelDetail
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @return string
     */
    public function getRegistrationCollapsed()
    {
        return $this->registrationCollapsed;
    }

    /**
     * @return Colour
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @return string
     */
    public function getVinCollapsed()
    {
        return $this->vinCollapsed;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return WeightSource
     */
    public function getWeightSource()
    {
        return $this->weightSource;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }
}
