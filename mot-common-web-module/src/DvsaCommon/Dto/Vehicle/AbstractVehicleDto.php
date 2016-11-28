<?php

namespace DvsaCommon\Dto\Vehicle;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\ColourCode;

/**
 * Common class for Vehicle Dto
 *
 * @package DvsaCommon\Dto\Vehicle
 */
abstract class AbstractVehicleDto extends AbstractDataTransferObject
{
    /** @var int */
    private $id;

    /** @var string */
    private $registration;
    /** @var string */
    private $vin;

    /** @var  int */
    private $emptyVrmReason;
    /** @var  int */
    private $emptyVinReason;

    /** @var \DateTime */
    private $manufactureDate;
    /** @var \DateTime */
    private $firstRegistrationDate;
    /** @var \DateTime */
    private $firstUsedDate;

    /** @var \DvsaCommon\Dto\VehicleClassification\VehicleClassDto */
    private $vehicleClass;

    /** @var ModelDetailDto */
    private $modelDetail;

    /** @var ColourDto */
    private $colourPrimary;
    /** @var ColourDto */
    private $colourSecondary;

    /** @var string */
    private $engineNumber;

    /** @var VehicleParamDto */
    private $fuelType;
    /** @var VehicleParamDto */
    private $bodyType;
    /** @var VehicleParamDto */
    private $transmissionType;
    /** @var int */
    private $cylinderCapacity;
    /** @var int */
    private $seatingCapacity;

    /** @var int */
    private $isNewAtFirstReg;

    /** @var string */
    private $makeName;

    /** @var string */
    private $modelName;

    public function __construct()
    {
        $this->vehicleClass = new VehicleClassDto;
        $this->modelDetail = new ModelDetailDto();
        $this->colourPrimary = new ColourDto;
        $this->colourSecondary = new ColourDto;
    }

    /**
     * @param string $id
     *
     * @return VehicleDto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $registration
     *
     * @return $this
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

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
     * @param string $vin
     *
     * @return $this
     */
    public function setVin($vin)
    {
        $this->vin = $vin;

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
     * @param string $reasonCode
     * @return $this
     */
    public function setEmptyVrmReason($reasonCode)
    {
        $this->emptyVrmReason = $reasonCode;
        return $this;
    }

    /**
      * @return string
     */
    public function getEmptyVrmReason()
    {
        return $this->emptyVrmReason;
    }

    /**
     * Sets the code of empty vin reason
     * @param string $reasonCode
     * @return $this
     */
    public function setEmptyVinReason($reasonCode)
    {
        $this->emptyVinReason = $reasonCode;
        return $this;
    }

    /**
     * Returns the code of empty vin reason
     * @return string
     */
    public function getEmptyVinReason()
    {
        return $this->emptyVinReason;
    }

    /**
     * @param \DateTime $manifactureDate
     *
     * @return VehicleDto
     */
    public function setManufactureDate($manifactureDate)
    {
        $this->manufactureDate = $manifactureDate;

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
     * @param \DateTime $date
     *
     * @return VehicleDto
     */
    public function setFirstRegistrationDate($date)
    {
        $this->firstRegistrationDate = $date;

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
     * @param string $firstUsedDate
     *
     * @return $this
     */
    public function setFirstUsedDate($firstUsedDate)
    {
        $this->firstUsedDate = $firstUsedDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstUsedDate()
    {
        return $this->firstUsedDate;
    }


    /**
     * @param \DvsaCommon\Dto\VehicleClassification\VehicleClassDto $vehicleClass
     *
     * @return $this
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;

        return $this;
    }

    /**
     * @return \DvsaCommon\Dto\VehicleClassification\VehicleClassDto
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * Return vehicle class code
     *
     * @return string
     */
    public function getClassCode()
    {
        return $this->getVehicleClass()->getCode();
    }

    /**
     * @param string $makeName
     *
     * @return $this
     */
    public function setMakeName($makeName)
    {
        $this->makeName = $makeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMakeName()
    {
        return $this->makeName;
    }

    /**
     * @param string $modelName
     *
     * @return $this
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;

        return $this;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param \DvsaEntities\Entity\Colour $colours
     *
     * @return $this
     */
    public function setColour($colour)
    {
        $this->colourPrimary = $colour;

        return $this;
    }

    /**
     * @return ColourDto
     */
    public function getColour()
    {
        return $this->colourPrimary;
    }

    /**
     * @param \DvsaEntities\Entity\Colour $colour
     *
     * @return $this
     */
    public function setColourSecondary($colour)
    {
        $this->colourSecondary = $colour;

        return $this;
    }

    /**
     * @return ColourDto
     */
    public function getColourSecondary()
    {
        return $this->colourSecondary;
    }

    public function getColoursNames()
    {
        $names = [];

        foreach ([$this->getColour(), $this->getColourSecondary()] as $colour) {
            if ($colour instanceof ColourDto
                && $colour->getCode() !== ColourCode::NOT_STATED
                && !empty($colour->getName())
            ) {
                $names[] = $colour->getName();
            }
        }

        return join(' and ', $names);
    }


    /**
     * @param string $engineNumber
     *
     * @return VehicleDto
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
     * @param VehicleParamDto $fuelType
     *
     * @return $this
     */
    public function setFuelType($fuelType)
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    /**
     * @return VehicleParamDto
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param VehicleParamDto $bodyType
     *
     * @return $this
     */
    public function setBodyType($bodyType)
    {
        $this->bodyType = $bodyType;

        return $this;
    }

    /**
     * @return VehicleParamDto
     */
    public function getBodyType()
    {
        return $this->bodyType;
    }

    /**
     * @param VehicleParamDto $transmissionType
     *
     * @return $this
     */
    public function setTransmissionType($transmissionType)
    {
        $this->transmissionType = $transmissionType;

        return $this;
    }

    /**
     * @return VehicleParamDto
     */
    public function getTransmissionType()
    {
        return $this->transmissionType;
    }

    /**
     * @param int $cylinderCapacity
     *
     * @return $this
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
     * @param int $seatingCapacity
     *
     * @return VehicleDto
     */
    public function setSeatingCapacity($seatingCapacity)
    {
        $this->seatingCapacity = $seatingCapacity;

        return $this;
    }

    /**
     * @return int
     */
    public function getSeatingCapacity()
    {
        return $this->seatingCapacity;
    }


    /**
     * @param boolean $value
     *
     * @return VehicleDto
     */
    public function setIsNewAtFirstReg($value)
    {
        $this->isNewAtFirstReg = (boolean)$value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNewAtFirstReg()
    {
        return $this->isNewAtFirstReg;
    }

    /**
     * @return string
     */
    public function getMakeAndModel()
    {
        return implode(', ', array_filter([$this->getMakeName(), $this->getModelName()]));
    }

    /**
     * What's the DB source of this vehicle: vehicle or dvla_vehicle? Child Dtos should implement this function and give
     * us the answer.
     *
     * @return bool
     */
    abstract public function isDvla();
}
