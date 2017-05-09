<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class Vehicle.
 *
 * @ORM\Table(name="vehicle")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\VehicleRepository")
 */
class Vehicle extends VehicleAbstract implements VehicleInterface
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
     * @var EmptyReasonMap
     *
     * @ORM\OneToOne(targetEntity="EmptyReasonMap", mappedBy="vehicleId")
     */
    private $emptyReasons;

    /**
     * @var VehicleHistory[]
     * @ORM\OneToMany(targetEntity="VehicleHistory", mappedBy="vehicle")
     */
    private $vehicleHistory = [];

    /**
     * @param MotTest $motTest
     *
     * @return Colour
     *
     * @throws \Exception
     */
    public function getColourDuringTest(MotTest $motTest)
    {
        return $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getColour();
    }

    /**
     * @param MotTest $motTest
     *
     * @return Colour
     *
     * @throws \Exception
     */
    public function getSecondaryColourDuringTest(MotTest $motTest)
    {
        return $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getSecondaryColour();
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
        if ($this->getModelDetail() &&
            $this->getModelDetail()->getModel() instanceof Model &&
            $this->getModelDetail()->getModel()->getMake() instanceof Make
        ) {
            return $this->getModelDetail()->getModel()->getMake()->getName();
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @return Model|null
     */
    public function getMakeDuringTest(MotTest $motTest)
    {
        $model = $this->getModelDuringTest($motTest);

        if ($model instanceof Model) {
            return $model->getMake();
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @return string|null
     */
    public function getMakeNameDuringTest(MotTest $motTest)
    {
        $make = $this->getMakeDuringTest($motTest);

        if ($make instanceof Make) {
            return $make->getName();
        }
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        if ($this->getModelDetail() &&
            $this->getModelDetail()->getModel() instanceof Model
        ) {
            return $this->getModelDetail()->getModel()->getName();
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @return string|null
     */
    public function getModelNameDuringTest(MotTest $motTest)
    {
        $model = $this->getModelDuringTest($motTest);

        if ($model instanceof Model) {
            return $model->getName();
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @return Model|null
     */
    public function getModelDuringTest(MotTest $motTest)
    {
        $modelDetail = $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getModelDetail();

        if ($modelDetail instanceof ModelDetail) {
            return $modelDetail->getModel();
        }
    }

    public function getCylinderCapacity()
    {
        return $this->getModelDetail()->getCylinderCapacity();
    }

    /**
     * @return EmptyVrmReason
     */
    public function getEmptyVrmReason()
    {
        if ($this->getEmptyReasons() instanceof EmptyReasonMap) {
            return $this->getEmptyReasons()->getEmptyVrmReason();
        }
    }

    /**
     * @return EmptyVinReason
     */
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

    /**
     * @param MotTest $motTest
     *
     * @return VehicleClass
     *
     * @throws \Exception
     */
    public function getVehicleClassDuringTest(MotTest $motTest)
    {
        $modelDetail = $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getModelDetail();

        if (!$modelDetail) {
            return;
        }

        return $modelDetail->getVehicleClass();
    }

    /**
     * @return VehicleClass
     */
    public function getVehicleClass()
    {
        return $this->getModelDetail()->getVehicleClass();
    }

    /**
     * @return BodyType
     */
    public function getBodyType()
    {
        return $this->getModelDetail()->getBodyType();
    }

    /**
     * @param MotTest $motTest
     *
     * @return FuelType|void
     *
     * @throws \Exception
     */
    public function getFuelTypeDuringTest(MotTest $motTest)
    {
        $modelDetail = $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getModelDetail();

        if (!$modelDetail) {
            return;
        }

        return $modelDetail->getFuelType();
    }

    /**
     * @return FuelType|void
     */
    public function getFuelType()
    {
        if (!$this->getModelDetail()) {
            return;
        }

        return $this->getModelDetail()->getFuelType();
    }

    /**
     * @param MotTest $motTest
     *
     * @return string
     */
    public function getRegistrationDuringTest(MotTest $motTest)
    {
        return $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getRegistration();
    }

    /**
     * @param MotTest $motTest
     *
     * @return string
     */
    public function getVinDuringTest(MotTest $motTest)
    {
        return $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getVin();
    }

    /**
     * @param MotTest $motTest
     *
     * @return ModelDetail
     */
    public function getModelDetailDuringTest(MotTest $motTest)
    {
        return $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getModelDetail();
    }

    /**
     * @param MotTest $motTest
     *
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistrationDuringTest(MotTest $motTest)
    {
        return $this->getVehicleDetailAtVersion($motTest->getVehicleVersion())->getCountryOfRegistration();
    }

    /**
     * @return VehicleHistory[]
     */
    public function getVehicleHistory()
    {
        return $this->vehicleHistory;
    }

    /**
     * @param int $version
     *
     * @return VehicleHistory
     *
     * @throws \Exception
     */
    private function getVehicleDetailAtVersion($version)
    {
        if ($this->getVersion() == $version) {
            return $this;
        }

        $triedIds = [];

        foreach ($this->getVehicleHistory() as $vehicle) {
            if ($vehicle->getVersion() == $version) {
                return $vehicle;
            }

            $triedIds[] = $vehicle->getId();
        }

        $historyChecked = empty($triedIds) ? '.' :
            sprintf(', also checked vehicle_hist with ID (%s)', implode(',', $triedIds));

        throw new \Exception(
            sprintf(
                'Failed to retrieve version %d for vehicle with ID %d%s',
                $version,
                $this->getId(),
                $historyChecked
            )
        );
    }

    /**
     * @param string $chassisNumber
     *
     * @return VehicleAbstract
     */
    public function setChassisNumber($chassisNumber)
    {
        $this->chassisNumber = $chassisNumber;

        return $this;
    }

    /**
     * @param Colour $colour
     *
     * @return VehicleAbstract
     */
    public function setColour($colour)
    {
        $this->colour = $colour;

        return $this;
    }

    /**
     * @param CountryOfRegistration $countryOfRegistration
     *
     * @return VehicleAbstract
     */
    public function setCountryOfRegistration($countryOfRegistration)
    {
        $this->countryOfRegistration = $countryOfRegistration;

        return $this;
    }

    /**
     * @param string $engineNumber
     *
     * @return VehicleAbstract
     */
    public function setEngineNumber($engineNumber)
    {
        $this->engineNumber = $engineNumber;

        return $this;
    }

    /**
     * @param \DateTime $firstRegistrationDate
     *
     * @return VehicleAbstract
     */
    public function setFirstRegistrationDate($firstRegistrationDate)
    {
        $this->firstRegistrationDate = $firstRegistrationDate;

        return $this;
    }

    /**
     * @param \DateTime $firstUsedDate
     *
     * @return VehicleAbstract
     */
    public function setFirstUsedDate($firstUsedDate)
    {
        $this->firstUsedDate = $firstUsedDate;

        return $this;
    }

    /**
     * @param bool $isDamaged
     *
     * @return VehicleAbstract
     */
    public function setIsDamaged($isDamaged)
    {
        $this->isDamaged = $isDamaged;

        return $this;
    }

    /**
     * @param bool $isDestroyed
     *
     * @return VehicleAbstract
     */
    public function setIsDestroyed($isDestroyed)
    {
        $this->isDestroyed = $isDestroyed;

        return $this;
    }

    /**
     * @param int $dvla_vehicle_id
     *
     * @return VehicleAbstract
     */
    public function setDvlaVehicleId($dvla_vehicle_id)
    {
        $this->dvla_vehicle_id = $dvla_vehicle_id;

        return $this;
    }

    /**
     * @param bool $isIncognito
     *
     * @return VehicleAbstract
     */
    public function setIsIncognito($isIncognito)
    {
        $this->isIncognito = $isIncognito;

        return $this;
    }

    /**
     * @param bool $newAtFirstReg
     *
     * @return VehicleAbstract
     */
    public function setNewAtFirstReg($newAtFirstReg)
    {
        $this->newAtFirstReg = $newAtFirstReg;

        return $this;
    }

    /**
     * @param \DateTime $manufactureDate
     *
     * @return VehicleAbstract
     */
    public function setManufactureDate($manufactureDate)
    {
        $this->manufactureDate = $manufactureDate;

        return $this;
    }

    /**
     * @param ModelDetail $modelDetail
     *
     * @return VehicleAbstract
     */
    public function setModelDetail($modelDetail)
    {
        $this->modelDetail = $modelDetail;

        return $this;
    }

    /**
     * @param string $registration
     *
     * @return VehicleAbstract
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * @param string $registrationCollapsed
     *
     * @return VehicleAbstract
     */
    public function setRegistrationCollapsed($registrationCollapsed)
    {
        $this->registrationCollapsed = $registrationCollapsed;

        return $this;
    }

    /**
     * @param Colour $secondaryColour
     *
     * @return VehicleAbstract
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;

        return $this;
    }

    /**
     * @param string $vin
     *
     * @return VehicleAbstract
     */
    public function setVin($vin)
    {
        $this->vin = $vin;

        return $this;
    }

    /**
     * @param string $vinCollapsed
     *
     * @return VehicleAbstract
     */
    public function setVinCollapsed($vinCollapsed)
    {
        $this->vinCollapsed = $vinCollapsed;

        return $this;
    }

    /**
     * @param int $weight
     *
     * @return VehicleAbstract
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @param WeightSource $weightSource
     *
     * @return VehicleAbstract
     */
    public function setWeightSource($weightSource)
    {
        $this->weightSource = $weightSource;

        return $this;
    }

    /**
     * @param int $year
     *
     * @return VehicleAbstract
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }
}
