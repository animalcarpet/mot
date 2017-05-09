<?php

namespace DvsaMotTest\ViewModel;

use Dvsa\Mot\ApiClient\Resource\Item\Colour;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\FuelType;
use Dvsa\Mot\ApiClient\Resource\Item\Make;
use Dvsa\Mot\ApiClient\Resource\Item\Model;
use Dvsa\Mot\ApiClient\Resource\Item\VehicleClass;
use DvsaCommon\Enum\ColourCode;

class DvsaVehicleViewModel
{
    /** @var string */
    private $amendedOn;

    /** @var string|null */
    private $emptyVrmReason;

    /** @var string|null */
    private $emptyVinReason;

    /** @var int|null */
    private $weight;

    /** @var int */
    private $version;

    /** @var int */
    private $id;

    /** @var string */
    private $registration;

    /** @var string */
    private $vin;

    /** @var Make */
    private $make;

    /** @var Model */
    private $model;

    /** @var Colour */
    private $colour;

    /** @var Colour */
    private $colourSecondary;

    /** @var int */
    private $countryofRegistrationId;

    /** @var FuelType */
    private $fuelType;

    /** @var VehicleClass */
    private $vehicleClass;

    /** @var string */
    private $bodyType;

    /** @var int */
    private $cylinderCapacity;

    /** @var string */
    private $transmissionType;

    /** @var string */
    private $firstRegistrationDate;

    /** @var string */
    private $firstUsedDate;

    /** @var string */
    private $manufactureDate;

    /** @var bool */
    private $isNewAtFirstReg;

    public function __construct(DvsaVehicle $dvsaVehicle)
    {
        $this->emptyVrmReason = $dvsaVehicle->getEmptyVrmReason();
        $this->emptyVinReason = $dvsaVehicle->getEmptyVinReason();
        $this->weight = $dvsaVehicle->getWeight();
        $this->version = $dvsaVehicle->getVersion();
        $this->amendedOn = $dvsaVehicle->getAmendedOn();
        $this->id = $dvsaVehicle->getId();
        $this->registration = $dvsaVehicle->getRegistration();
        $this->vin = $dvsaVehicle->getVin();
        $this->make = $dvsaVehicle->getMake();
        $this->model = $dvsaVehicle->getModel();
        $this->colour = $dvsaVehicle->getColour();
        $this->colourSecondary = $dvsaVehicle->getColourSecondary();
        $this->countryofRegistrationId = $dvsaVehicle->getCountryOfRegistrationId();
        $this->fuelType = $dvsaVehicle->getFuelType();
        $this->vehicleClass = $dvsaVehicle->getVehicleClass();
        $this->bodyType = $dvsaVehicle->getBodyType();
        $this->cylinderCapacity = $dvsaVehicle->getCylinderCapacity();
        $this->transmissionType = $dvsaVehicle->getTransmissionType();
        $this->firstUsedDate = $dvsaVehicle->getFirstUsedDate();
        $this->firstRegistrationDate = $dvsaVehicle->getFirstRegistrationDate();
        $this->manufactureDate = $dvsaVehicle->getManufactureDate();
        $this->isNewAtFirstReg = $dvsaVehicle->getIsNewAtFirstReg();
    }

    /**
     * @return string
     */
    public function getAmendedOn()
    {
        return $this->amendedOn;
    }

    /**
     * @param string $amendedOn
     */
    public function setAmendedOn($amendedOn)
    {
        $this->amendedOn = $amendedOn;
    }

    /**
     * @return null|string
     */
    public function getEmptyVrmReason()
    {
        return $this->emptyVrmReason;
    }

    /**
     * @param null|string $emptyVrmReason
     */
    public function setEmptyVrmReason($emptyVrmReason)
    {
        $this->emptyVrmReason = $emptyVrmReason;
    }

    /**
     * @return null|string
     */
    public function getEmptyVinReason()
    {
        return $this->emptyVinReason;
    }

    /**
     * @param null|string $emptyVinReason
     */
    public function setEmptyVinReason($emptyVinReason)
    {
        $this->emptyVinReason = $emptyVinReason;
    }

    /**
     * @return int|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int|null $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        if ($this->registration !== null || empty($this->registration)) {
            return strtoupper($this->registration);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param string $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return strtoupper($this->vin);
    }

    /**
     * @param string $vin
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
    }

    /**
     * @return Make
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param Make $make
     */
    public function setMake($make)
    {
        $this->make = $make;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
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
     */
    public function setColour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * @return Colour
     */
    public function getColourSecondary()
    {
        return $this->colourSecondary;
    }

    /**
     * @param Colour $colourSecondary
     */
    public function setColourSecondary($colourSecondary)
    {
        $this->colourSecondary = $colourSecondary;
    }

    /**
     * @return string|null
     */
    private function getSecondaryColourName()
    {
        if (is_null($this->getColourSecondary()) ||
            ColourCode::NOT_STATED == $this->getColourSecondary()->getCode()
        ) {
            return;
        }

        return $this->getColourSecondary()->getName();
    }

    /**
     * @return string
     */
    public function getColours()
    {
        $primaryColourName = $this->getColour()->getName();
        $secondaryColourName = $this->getSecondaryColourName();

        return !empty($secondaryColourName) ? $primaryColourName.' and '.$secondaryColourName :
            $primaryColourName;
    }

    /**
     * @return int
     */
    public function getCountryofRegistrationId()
    {
        return $this->countryofRegistrationId;
    }

    /**
     * @param int $countryofRegistrationId
     */
    public function setCountryofRegistration($countryofRegistrationId)
    {
        $this->countryofRegistrationId = $countryofRegistrationId;
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
     */
    public function setFuelTypeCode($fuelType)
    {
        $this->fuelType = $fuelType;
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
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;
    }

    /**
     * @return string
     */
    public function getBodyType()
    {
        return $this->bodyType;
    }

    /**
     * @param string $bodyType
     */
    public function setBodyType($bodyType)
    {
        $this->bodyType = $bodyType;
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
     */
    public function setCylinderCapacity($cylinderCapacity)
    {
        $this->cylinderCapacity = $cylinderCapacity;
    }

    /**
     * @return string
     */
    public function getTransmissionType()
    {
        return $this->transmissionType;
    }

    /**
     * @param string $transmissionType
     */
    public function setTransmissionType($transmissionType)
    {
        $this->transmissionType = $transmissionType;
    }

    /**
     * @return string
     */
    public function getFirstRegistrationDate()
    {
        return $this->firstRegistrationDate;
    }

    /**
     * @param string $firstRegistrationDate
     */
    public function setFirstRegistrationDate($firstRegistrationDate)
    {
        $this->firstRegistrationDate = $firstRegistrationDate;
    }

    /**
     * @return string
     */
    public function getFirstUsedDate()
    {
        return $this->firstUsedDate;
    }

    /**
     * @param string $firstUsedDate
     */
    public function setFirstUsedDate($firstUsedDate)
    {
        $this->firstUsedDate = $firstUsedDate;
    }

    /**
     * @return string
     */
    public function getManufactureDate()
    {
        return $this->manufactureDate;
    }

    /**
     * @param string $manufactureDate
     */
    public function setManufactureDate($manufactureDate)
    {
        $this->manufactureDate = $manufactureDate;
    }

    /**
     * @return bool
     */
    public function isNewAtFirstReg()
    {
        return $this->isNewAtFirstReg;
    }

    /**
     * @param bool $isNewAtFirstReg
     */
    public function setIsNewAtFirstReg($isNewAtFirstReg)
    {
        $this->isNewAtFirstReg = $isNewAtFirstReg;
    }

    public function getMakeAndModel()
    {
        return implode(', ', array_filter([$this->getMake()->getName(), $this->getModel()->getName()]));
    }
}
